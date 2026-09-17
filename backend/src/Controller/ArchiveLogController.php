<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Entity\Mule;
use App\Repository\CharactersRepository;
use App\Repository\MuleRepository;
use App\Service\GuildLogParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Archives characters/mules who left the guild, based on a pasted Dofus guild log, with
 * optional manual per-item adjustments (`excludeKeys` to drop a detected/cascaded row,
 * `additionalKeys` to bring in a character/mule the log didn't mention). `analyze` never
 * writes anything; `execute` re-runs the exact same classification server-side (rather than
 * trusting the client's view of who is "already archived" or "found") so the archive
 * operation always reflects real, current database state and stays idempotent.
 */
class ArchiveLogController extends AbstractController
{
    #[Route('/archive-log/analyze', name: 'archive_log_analyze', methods: ['POST'])]
    public function analyze(
        Request $request,
        GuildLogParserService $parser,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository
    ): JsonResponse {
        $payload = $this->extractPayload($request);
        if ($payload === null) {
            return $this->json(['error' => 'Le champ "log" est requis et doit être une chaîne non vide.'], 400);
        }

        $classified = $this->buildClassification($payload, $parser, $charactersRepository, $muleRepository);

        return $this->json([
            'summary' => $this->buildSummary($classified),
            'items' => $this->serializeItems($classified['items']),
        ]);
    }

    #[Route('/archive-log/execute', name: 'archive_log_execute', methods: ['POST'])]
    public function execute(
        Request $request,
        GuildLogParserService $parser,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $payload = $this->extractPayload($request);
        if ($payload === null) {
            return $this->json(['error' => 'Le champ "log" est requis et doit être une chaîne non vide.'], 400);
        }

        $classified = $this->buildClassification($payload, $parser, $charactersRepository, $muleRepository);
        $classified['items'] = $this->archiveToArchiveItems($classified['items'], $em);

        return $this->json([
            'summary' => $this->buildSummary($classified),
            'items' => $this->serializeItems($classified['items']),
        ]);
    }

    /**
     * Sets isArchived=true on every matched entity flagged "to_archive", in a single
     * transaction so a failure midway never leaves a partially-archived batch. Returns the
     * items with their status flipped to "archived" so the response reflects what actually
     * happened, instead of still saying "to_archive" for entries that were just archived.
     */
    private function archiveToArchiveItems(array $items, EntityManagerInterface $em): array
    {
        $em->wrapInTransaction(function () use (&$items): void {
            foreach ($items as &$item) {
                if ($item['status'] === 'to_archive') {
                    $item['entity']->setIsArchived(true);
                    $item['status'] = 'archived';
                }
            }
            unset($item);
        });

        return $items;
    }

    private function extractPayload(Request $request): ?array
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['log']) || !is_string($data['log']) || trim($data['log']) === '') {
            return null;
        }

        return [
            'log' => $data['log'],
            'excludeKeys' => $this->extractStringArray($data['excludeKeys'] ?? []),
            'additionalKeys' => $this->extractStringArray($data['additionalKeys'] ?? []),
        ];
    }

    private function extractStringArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }

    /**
     * Parses the log and matches it against Characters/Mules, then layers in the manual
     * additions, cascades archived characters' mules (log-derived and manually-added alike),
     * and finally drops anything the user manually excluded.
     *
     * @return array{detectedCount: int, uniqueCount: int, items: array}
     */
    private function buildClassification(
        array $payload,
        GuildLogParserService $parser,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository
    ): array {
        $classified = $this->classify($payload['log'], $parser, $charactersRepository, $muleRepository);

        $additionalItems = $this->resolveAdditionalItems(
            $payload['additionalKeys'],
            $classified['items'],
            $charactersRepository,
            $muleRepository
        );

        $items = array_merge($classified['items'], $additionalItems);
        $items = array_merge($items, $this->cascadeMulesForArchivedCharacters($items));
        $items = $this->applyExclusions($items, $payload['excludeKeys']);

        $classified['items'] = $items;

        return $classified;
    }

    /**
     * Parses the log, deduplicates the pseudos, then matches each one against
     * Characters and Mules in two batched (case-insensitive) queries.
     *
     * @return array{detectedCount: int, uniqueCount: int, items: array}
     */
    private function classify(
        string $log,
        GuildLogParserService $parser,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository
    ): array {
        $parsed = $parser->parse($log);
        $uniquePseudos = $parsed['uniquePseudos'];

        $charactersByKey = [];
        foreach ($charactersRepository->findByPseudosCaseInsensitive($uniquePseudos) as $character) {
            $charactersByKey[mb_strtolower($character->getPseudo())] = $character;
        }

        $mulesByKey = [];
        foreach ($muleRepository->findByPseudosCaseInsensitive($uniquePseudos) as $mule) {
            $mulesByKey[mb_strtolower($mule->getPseudo())] = $mule;
        }

        $items = [];
        foreach ($uniquePseudos as $logPseudo) {
            $key = mb_strtolower($logPseudo);

            if (isset($charactersByKey[$key])) {
                $character = $charactersByKey[$key];
                $items[] = [
                    'logPseudo' => $logPseudo,
                    'type' => 'character',
                    'entity' => $character,
                    'status' => $character->isArchived() ? 'already_archived' : 'to_archive',
                    'cascadedFromCharacterId' => null,
                ];
            } elseif (isset($mulesByKey[$key])) {
                $mule = $mulesByKey[$key];
                $items[] = [
                    'logPseudo' => $logPseudo,
                    'type' => 'mule',
                    'entity' => $mule,
                    'status' => $mule->isArchived() ? 'already_archived' : 'to_archive',
                    'cascadedFromCharacterId' => null,
                ];
            } else {
                $items[] = [
                    'logPseudo' => $logPseudo,
                    'type' => 'not_found',
                    'entity' => null,
                    'status' => 'not_found',
                    'cascadedFromCharacterId' => null,
                ];
            }
        }

        return [
            'detectedCount' => $parsed['detectedCount'],
            'uniqueCount' => count($uniquePseudos),
            'items' => $items,
        ];
    }

    /**
     * Resolves "character:<id>" / "mule:<id>" keys from the client's manual "add a member"
     * search into real, freshly-looked-up entities. Unknown ids, malformed keys, and keys
     * already present in the current item list are silently skipped.
     *
     * @param string[] $additionalKeys
     */
    private function resolveAdditionalItems(
        array $additionalKeys,
        array $existingItems,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository
    ): array {
        if (empty($additionalKeys)) {
            return [];
        }

        $seenKeys = [];
        foreach ($existingItems as $item) {
            $seenKeys[$this->itemKey($item)] = true;
        }

        $items = [];
        foreach ($additionalKeys as $rawKey) {
            if (isset($seenKeys[$rawKey]) || !preg_match('/^(character|mule):(\d+)$/', $rawKey, $matches)) {
                continue;
            }

            [, $type, $id] = $matches;
            $entity = $type === 'character' ? $charactersRepository->find((int) $id) : $muleRepository->find((int) $id);
            if (!$entity) {
                continue;
            }

            $seenKeys[$rawKey] = true;
            $items[] = [
                'logPseudo' => $entity->getPseudo(),
                'type' => $type,
                'entity' => $entity,
                'status' => $entity->isArchived() ? 'already_archived' : 'to_archive',
                'cascadedFromCharacterId' => null,
            ];
        }

        return $items;
    }

    /**
     * Archiving a character always cascades to its mules elsewhere in the app (see
     * CharactersController::updateIsArchived) — mirror that here, and surface it in the
     * preview already, so a character is never archived while its mules stay active, and
     * the user sees the full picture before confirming. Returns only the newly-added items.
     */
    private function cascadeMulesForArchivedCharacters(array $items): array
    {
        $seenMuleIds = [];
        foreach ($items as $item) {
            if ($item['type'] === 'mule') {
                $seenMuleIds[$item['entity']->getId()] = true;
            }
        }

        $newItems = [];
        foreach ($items as $item) {
            if ($item['type'] !== 'character' || $item['status'] !== 'to_archive') {
                continue;
            }

            foreach ($item['entity']->getMules() as $mule) {
                if ($mule->isArchived() || isset($seenMuleIds[$mule->getId()])) {
                    continue;
                }

                $seenMuleIds[$mule->getId()] = true;
                $newItems[] = [
                    'logPseudo' => $mule->getPseudo(),
                    'type' => 'mule',
                    'entity' => $mule,
                    'status' => 'to_archive',
                    'cascadedFromCharacterId' => $item['entity']->getId(),
                ];
            }
        }

        return $newItems;
    }

    /**
     * A mule that's only in the list because its character cascaded it there leaves with
     * that character — unless it was independently matched in the log or added on its own,
     * in which case it's kept regardless of what happens to the character.
     *
     * @param string[] $excludeKeys
     */
    private function applyExclusions(array $items, array $excludeKeys): array
    {
        if (empty($excludeKeys)) {
            return $items;
        }

        $excludeSet = array_flip($excludeKeys);

        $excludedCharacterIds = [];
        foreach ($excludeKeys as $key) {
            if (preg_match('/^character:(\d+)$/', $key, $matches)) {
                $excludedCharacterIds[(int) $matches[1]] = true;
            }
        }

        return array_values(array_filter($items, function (array $item) use ($excludeSet, $excludedCharacterIds) {
            if (isset($excludeSet[$this->itemKey($item)])) {
                return false;
            }

            $cascadedFrom = $item['cascadedFromCharacterId'] ?? null;

            return $cascadedFrom === null || !isset($excludedCharacterIds[$cascadedFrom]);
        }));
    }

    /**
     * Stable identifier for an item, used by the frontend to reference a specific row when
     * excluding it, and to avoid re-adding a row that's already present.
     */
    private function itemKey(array $item): string
    {
        if ($item['type'] === 'not_found') {
            return 'not_found:' . $item['logPseudo'];
        }

        return $item['type'] . ':' . $item['entity']->getId();
    }

    private function serializeItems(array $items): array
    {
        return array_map(function (array $item) {
            /** @var Characters|Mule|null $entity */
            $entity = $item['entity'];

            return [
                'key' => $this->itemKey($item),
                'logPseudo' => $item['logPseudo'],
                'pseudo' => $entity?->getPseudo() ?? $item['logPseudo'],
                'type' => $item['type'],
                'class' => $entity?->getClass(),
                'id' => $entity?->getId(),
                'mainCharacterPseudo' => $item['type'] === 'mule' ? $entity?->getMainCharacter()?->getPseudo() : null,
                'status' => $item['status'],
            ];
        }, $items);
    }

    private function buildSummary(array $classified): array
    {
        $toArchiveCount = 0;
        $alreadyArchivedCount = 0;
        $notFoundCount = 0;

        foreach ($classified['items'] as $item) {
            match ($item['status']) {
                'to_archive', 'archived' => $toArchiveCount++,
                'already_archived' => $alreadyArchivedCount++,
                'not_found' => $notFoundCount++,
            };
        }

        return [
            'detectedCount' => $classified['detectedCount'],
            'uniqueCount' => $classified['uniqueCount'],
            'duplicateCount' => $classified['detectedCount'] - $classified['uniqueCount'],
            'toArchiveCount' => $toArchiveCount,
            'alreadyArchivedCount' => $alreadyArchivedCount,
            'notFoundCount' => $notFoundCount,
        ];
    }
}
