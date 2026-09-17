<?php

namespace App\Tests\Controller;

use App\Controller\ArchiveLogController;
use App\Entity\Characters;
use App\Entity\Mule;
use App\Repository\CharactersRepository;
use App\Repository\MuleRepository;
use App\Service\GuildLogParserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the controller's business logic directly through reflection (classify /
 * buildClassification / buildSummary / serializeItems / archiveToArchiveItems), bypassing
 * routing and AbstractController::json()'s container dependency entirely. Repositories are
 * mocked to return plain entities built in memory, so this never touches the shared
 * Postgres instance used for manual testing.
 */
class ArchiveLogControllerTest extends TestCase
{
    private function callPrivate(object $object, string $method, array $args = [])
    {
        $ref = new \ReflectionMethod($object, $method);
        $ref->setAccessible(true);

        return $ref->invokeArgs($object, $args);
    }

    private function payload(string $log, array $excludeKeys = [], array $additionalKeys = []): array
    {
        return ['log' => $log, 'excludeKeys' => $excludeKeys, 'additionalKeys' => $additionalKeys];
    }

    private function makeCharacter(int $id, string $pseudo, string $class, bool $isArchived): Characters
    {
        $character = new Characters();
        $idProp = new \ReflectionProperty(Characters::class, 'id');
        $idProp->setAccessible(true);
        $idProp->setValue($character, $id);
        $character->setPseudo($pseudo)->setAnkamaPseudo($pseudo)->setClass($class)->setIsArchived($isArchived);

        return $character;
    }

    private function makeMule(int $id, string $pseudo, string $class, bool $isArchived, ?Characters $main = null): Mule
    {
        $mule = new Mule();
        $idProp = new \ReflectionProperty(Mule::class, 'id');
        $idProp->setAccessible(true);
        $idProp->setValue($mule, $id);
        $mule->setPseudo($pseudo)->setAnkamaPseudo($pseudo)->setClass($class)->setIsArchived($isArchived);
        if ($main) {
            $mule->setMainCharacter($main);
        }

        return $mule;
    }

    public function testClassifyDistinguishesCharacterMuleAndNotFound(): void
    {
        $activeCharacter = $this->makeCharacter(1, 'Red-Huissier', 'iop', false);
        $alreadyArchivedMule = $this->makeMule(2, 'Worldeater', 'sacrieur', true);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$activeCharacter]);

        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([$alreadyArchivedMule]);

        $controller = new ArchiveLogController();
        $log = "[20:39] Red-Huissier ne fait plus partie de la guilde.\n"
            . "[20:39] Worldeater ne fait plus partie de la guilde.\n"
            . "[20:39] Toto ne fait plus partie de la guilde.";

        $classified = $this->callPrivate($controller, 'classify', [
            $log, new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);
        $summary = $this->callPrivate($controller, 'buildSummary', [$classified]);
        $items = $this->callPrivate($controller, 'serializeItems', [$classified['items']]);

        $this->assertSame(3, $summary['detectedCount']);
        $this->assertSame(1, $summary['toArchiveCount']);
        $this->assertSame(1, $summary['alreadyArchivedCount']);
        $this->assertSame(1, $summary['notFoundCount']);

        $byPseudo = array_column($items, null, 'pseudo');
        $this->assertSame('character', $byPseudo['Red-Huissier']['type']);
        $this->assertSame('to_archive', $byPseudo['Red-Huissier']['status']);
        $this->assertSame('character:1', $byPseudo['Red-Huissier']['key']);
        $this->assertSame('mule', $byPseudo['Worldeater']['type']);
        $this->assertSame('already_archived', $byPseudo['Worldeater']['status']);
        $this->assertSame('not_found', $byPseudo['Toto']['type']);
        $this->assertSame('not_found:Toto', $byPseudo['Toto']['key']);
    }

    public function testExtractPayloadRejectsMissingOrEmptyLogAndDefaultsAdjustmentArrays(): void
    {
        $controller = new ArchiveLogController();

        $emptyRequest = new \Symfony\Component\HttpFoundation\Request([], [], [], [], [], [], json_encode(['log' => '   ']));
        $missingRequest = new \Symfony\Component\HttpFoundation\Request([], [], [], [], [], [], json_encode([]));

        $this->assertNull($this->callPrivate($controller, 'extractPayload', [$emptyRequest]));
        $this->assertNull($this->callPrivate($controller, 'extractPayload', [$missingRequest]));

        $validRequest = new \Symfony\Component\HttpFoundation\Request([], [], [], [], [], [], json_encode([
            'log' => 'Bardrum ne fait plus partie de la guilde.',
            'excludeKeys' => ['character:1', 42, null],
            'additionalKeys' => ['mule:2'],
        ]));
        $payload = $this->callPrivate($controller, 'extractPayload', [$validRequest]);

        $this->assertSame('Bardrum ne fait plus partie de la guilde.', $payload['log']);
        $this->assertSame(['character:1'], $payload['excludeKeys']); // non-string entries dropped
        $this->assertSame(['mule:2'], $payload['additionalKeys']);

        $noAdjustmentsRequest = new \Symfony\Component\HttpFoundation\Request([], [], [], [], [], [], json_encode(['log' => 'x ne fait plus partie de la guilde.']));
        $defaultPayload = $this->callPrivate($controller, 'extractPayload', [$noAdjustmentsRequest]);
        $this->assertSame([], $defaultPayload['excludeKeys']);
        $this->assertSame([], $defaultPayload['additionalKeys']);
    }

    public function testArchiveToArchiveItemsArchivesOnlyMatchedToArchiveEntities(): void
    {
        $character = $this->makeCharacter(1, 'Red-Huissier', 'iop', false);
        $alreadyArchivedMule = $this->makeMule(2, 'Worldeater', 'sacrieur', true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('wrapInTransaction')->willReturnCallback(fn (callable $cb) => $cb());

        $controller = new ArchiveLogController();
        $items = [
            ['logPseudo' => 'Red-Huissier', 'type' => 'character', 'entity' => $character, 'status' => 'to_archive'],
            ['logPseudo' => 'Worldeater', 'type' => 'mule', 'entity' => $alreadyArchivedMule, 'status' => 'already_archived'],
            ['logPseudo' => 'Toto', 'type' => 'not_found', 'entity' => null, 'status' => 'not_found'],
        ];

        $result = $this->callPrivate($controller, 'archiveToArchiveItems', [$items, $em]);

        $this->assertTrue($character->isArchived());
        $this->assertTrue($alreadyArchivedMule->isArchived()); // unchanged, was already true

        $byPseudo = array_column($result, null, 'logPseudo');
        $this->assertSame('archived', $byPseudo['Red-Huissier']['status']);
        $this->assertSame('already_archived', $byPseudo['Worldeater']['status']);
        $this->assertSame('not_found', $byPseudo['Toto']['status']);

        $summary = $this->callPrivate($controller, 'buildSummary', [
            ['detectedCount' => 3, 'uniqueCount' => 3, 'items' => $result],
        ]);
        $this->assertSame(1, $summary['toArchiveCount']);
        $this->assertSame(1, $summary['alreadyArchivedCount']);
        $this->assertSame(1, $summary['notFoundCount']);
    }

    public function testBuildClassificationCascadesArchivingToTheCharactersMules(): void
    {
        // Archiving a character must always cascade to its mules (see CharactersController::
        // updateIsArchived), even when only the main character's pseudo appears in the log.
        $character = $this->makeCharacter(1, 'Bardrum', 'iop', false);
        $activeMule = $this->makeMule(10, 'Bardrum-Mule', 'sadida', false, $character);
        $alreadyArchivedMule = $this->makeMule(11, 'Bardrum-OldMule', 'xelor', true, $character);
        $character->addMule($activeMule);
        $character->addMule($alreadyArchivedMule);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$character]);
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([]);

        $controller = new ArchiveLogController();
        $log = '[20:39] Bardrum ne fait plus partie de la guilde.';

        $classified = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log), new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);

        $byPseudo = array_column($classified['items'], null, 'logPseudo');
        $this->assertCount(2, $classified['items']); // character + its one not-yet-archived mule
        $this->assertSame('to_archive', $byPseudo['Bardrum']['status']);
        $this->assertSame('to_archive', $byPseudo['Bardrum-Mule']['status']);
        $this->assertArrayNotHasKey('Bardrum-OldMule', $byPseudo); // already archived, not re-listed

        // Executing must actually flip the cascaded mule to archived too.
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('wrapInTransaction')->willReturnCallback(fn (callable $cb) => $cb());
        $archived = $this->callPrivate($controller, 'archiveToArchiveItems', [$classified['items'], $em]);

        $this->assertTrue($character->isArchived());
        $this->assertTrue($activeMule->isArchived());
        $archivedByPseudo = array_column($archived, null, 'logPseudo');
        $this->assertSame('archived', $archivedByPseudo['Bardrum-Mule']['status']);
    }

    public function testBuildClassificationDoesNotDuplicateAMuleAlreadyMatchedDirectlyFromTheLog(): void
    {
        $character = $this->makeCharacter(1, 'Bardrum', 'iop', false);
        $mule = $this->makeMule(10, 'Bardrum-Mule', 'sadida', false, $character);
        $character->addMule($mule);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$character]);
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([$mule]);

        $controller = new ArchiveLogController();
        $log = "[20:39] Bardrum ne fait plus partie de la guilde.\n"
            . '[20:40] Bardrum-Mule ne fait plus partie de la guilde.';

        $classified = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log), new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);

        // The mule was already in the log directly — the cascade must not add a second row for it.
        $this->assertCount(2, $classified['items']);
    }

    public function testBuildClassificationAddsAManuallySpecifiedCharacterAndCascadesItsMules(): void
    {
        // The log only mentions an unrelated departure; the admin manually searched for and
        // added "Siisko" (id 42), who isn't in the log at all.
        $logCharacter = $this->makeCharacter(1, 'Red-Huissier', 'iop', false);
        $manuallyAdded = $this->makeCharacter(42, 'Siisko', 'cra', false);
        $manuallyAddedMule = $this->makeMule(43, 'Siisko-Mule', 'feca', false, $manuallyAdded);
        $manuallyAdded->addMule($manuallyAddedMule);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$logCharacter]);
        $charactersRepository->method('find')->with(42)->willReturn($manuallyAdded);
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([]);

        $controller = new ArchiveLogController();
        $log = '[20:39] Red-Huissier ne fait plus partie de la guilde.';

        $classified = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log, [], ['character:42']),
            new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);

        $byPseudo = array_column($classified['items'], null, 'logPseudo');
        $this->assertCount(3, $classified['items']); // log character + manually-added character + its cascaded mule
        $this->assertSame('to_archive', $byPseudo['Siisko']['status']);
        $this->assertSame('to_archive', $byPseudo['Siisko-Mule']['status']);
    }

    public function testBuildClassificationIgnoresUnknownOrMalformedAdditionalKeys(): void
    {
        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([]);
        $charactersRepository->method('find')->willReturn(null); // simulates an id that no longer exists
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([]);

        $controller = new ArchiveLogController();
        $log = 'Un-Pseudo-Introuvable ne fait plus partie de la guilde.';

        $classified = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log, [], ['character:999', 'not-a-valid-key', 'sram:1']),
            new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);

        // Only the one not_found item from the log itself — nothing manually added.
        $this->assertCount(1, $classified['items']);
        $this->assertSame('not_found', $classified['items'][0]['type']);
    }

    public function testBuildClassificationExcludesManuallyRemovedItem(): void
    {
        $character = $this->makeCharacter(1, 'Bardrum', 'iop', false);
        $mule = $this->makeMule(10, 'Bardrum-Mule', 'sadida', false, $character);
        $character->addMule($mule);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$character]);
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([]);

        $controller = new ArchiveLogController();
        $log = '[20:39] Bardrum ne fait plus partie de la guilde.';

        // Exclude only the cascaded mule — the character stays, the mule doesn't.
        $classified = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log, ['mule:10'], []),
            new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);

        $this->assertCount(1, $classified['items']);
        $this->assertSame('character', $classified['items'][0]['type']);

        // Excluding the character itself removes it too.
        $classifiedNoCharacter = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log, ['character:1'], []),
            new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);
        $this->assertCount(0, $classifiedNoCharacter['items']); // character + its cascaded mule both leave together
    }

    public function testExcludingACharacterKeepsAMuleThatWasIndependentlyMatchedInTheLog(): void
    {
        // Unlike a purely-cascaded mule, one the log (or a manual add) mentions on its own
        // must survive its character being excluded — the admin asked for it specifically.
        $character = $this->makeCharacter(1, 'Bardrum', 'iop', false);
        $mule = $this->makeMule(10, 'Bardrum-Mule', 'sadida', false, $character);
        $character->addMule($mule);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$character]);
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([$mule]);

        $controller = new ArchiveLogController();
        $log = "[20:39] Bardrum ne fait plus partie de la guilde.\n"
            . '[20:40] Bardrum-Mule ne fait plus partie de la guilde.';

        $classified = $this->callPrivate($controller, 'buildClassification', [
            $this->payload($log, ['character:1'], []),
            new GuildLogParserService(), $charactersRepository, $muleRepository,
        ]);

        $this->assertCount(1, $classified['items']);
        $this->assertSame('mule', $classified['items'][0]['type']);
    }

    public function testClassifyIsIdempotentOnceEntityIsArchived(): void
    {
        // Simulates running /execute twice in a row against the same log: the second
        // pass must see the character as already archived rather than re-flagging it.
        $character = $this->makeCharacter(1, 'Red-Huissier', 'iop', false);

        $charactersRepository = $this->createMock(CharactersRepository::class);
        $charactersRepository->method('findByPseudosCaseInsensitive')->willReturn([$character]);
        $muleRepository = $this->createMock(MuleRepository::class);
        $muleRepository->method('findByPseudosCaseInsensitive')->willReturn([]);

        $controller = new ArchiveLogController();
        $log = '[20:39] Red-Huissier ne fait plus partie de la guilde.';

        $firstPass = $this->callPrivate($controller, 'classify', [$log, new GuildLogParserService(), $charactersRepository, $muleRepository]);
        $this->assertSame('to_archive', $firstPass['items'][0]['status']);

        $character->setIsArchived(true);

        $secondPass = $this->callPrivate($controller, 'classify', [$log, new GuildLogParserService(), $charactersRepository, $muleRepository]);
        $this->assertSame('already_archived', $secondPass['items'][0]['status']);
    }
}
