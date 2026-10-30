<?php

namespace App\Controller;

use App\Service\Gw2\Gw2ApiClient;
use App\Service\Gw2\Gw2ApiException;
use App\Service\Gw2\Gw2InventoryService;
use App\Service\Gw2\Gw2ItemFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Private Guild Wars 2 section. ROLE_DAMS only (also enforced by access_control on ^/gw2).
 * Every call to the GW2 API goes through Gw2ApiClient so the API key never reaches the browser.
 */
#[IsGranted('ROLE_DAMS')]
class Gw2Controller extends AbstractController
{
    use Gw2ErrorResponses;

    public function __construct(
        private Gw2ApiClient $gw2,
        private Gw2ItemFormatter $formatter,
    ) {
    }

    #[Route('/gw2/tokeninfo', name: 'gw2_tokeninfo', methods: ['GET'])]
    public function tokenInfo(): JsonResponse
    {
        return $this->handle(function () {
            $info = $this->gw2->getTokenInfo();
            $permissions = $info['permissions'] ?? [];

            // The token id is part of the key itself: only the name and permissions are exposed
            return [
                'name' => $info['name'] ?? null,
                'permissions' => $permissions,
                'expected' => Gw2ApiClient::EXPECTED_PERMISSIONS,
                'missing' => array_values(array_diff(Gw2ApiClient::EXPECTED_PERMISSIONS, $permissions)),
            ];
        });
    }

    #[Route('/gw2/characters', name: 'gw2_characters', methods: ['GET'])]
    public function characters(): JsonResponse
    {
        return $this->handle(function () {
            $cores = $this->gw2->getCharacterCores($this->gw2->getCharacterNames());

            return array_values(array_map(fn (array $core) => [
                'name' => $core['name'] ?? null,
                'race' => $core['race'] ?? null,
                'gender' => $core['gender'] ?? null,
                'profession' => $core['profession'] ?? null,
                'level' => $core['level'] ?? null,
                'age' => $core['age'] ?? null,
                'deaths' => $core['deaths'] ?? null,
                'created' => $core['created'] ?? null,
            ], $cores));
        });
    }

    #[Route('/gw2/characters/{name}', name: 'gw2_character', methods: ['GET'])]
    public function character(string $name): JsonResponse
    {
        return $this->handle(fn () => $this->formatCharacter($this->gw2->getCharacter($name)));
    }

    /**
     * Bags of every character, shared slots and bank, with prices and sell/salvage advice.
     * ?refresh=1 bypasses the server cache.
     */
    #[Route('/gw2/inventory', name: 'gw2_inventory', methods: ['GET'])]
    public function inventory(Request $request, Gw2InventoryService $inventory): JsonResponse
    {
        return $this->handle(fn () => $inventory->build($request->query->getBoolean('refresh')));
    }

    private function formatCharacter(array $character): array
    {
        // Specializations of the active build tab (null ids = empty trait line)
        $activeBuild = null;
        foreach ($character['build_tabs'] ?? [] as $tab) {
            if (!empty($tab['is_active'])) {
                $activeBuild = $tab['build'] ?? null;
            }
        }
        $specIds = array_filter(array_column($activeBuild['specializations'] ?? [], 'id'));
        $specs = $specIds ? $this->gw2->getSpecializations($specIds) : [];

        // Equipment worn on the active equipment tab
        $activeEquipmentTab = $character['active_equipment_tab'] ?? null;
        $equipped = array_values(array_filter(
            $character['equipment'] ?? [],
            fn (array $e) => in_array($activeEquipmentTab, $e['tabs'] ?? [], true)
                && str_starts_with($e['location'] ?? 'Equipped', 'Equipped'),
        ));

        $bagSlots = 0;
        $usedSlots = 0;
        foreach ($character['bags'] ?? [] as $bag) {
            if (null === $bag) {
                continue;
            }
            $bagSlots += $bag['size'] ?? 0;
            $usedSlots += count(array_filter($bag['inventory'] ?? []));
        }

        $guild = null;
        if (!empty($character['guild'])) {
            try {
                $guildData = $this->gw2->getGuild($character['guild']);
                $guild = ['name' => $guildData['name'] ?? null, 'tag' => $guildData['tag'] ?? null];
            } catch (Gw2ApiException) {
                // The guild name is only decoration, never fail the whole page for it
            }
        }

        return [
            'name' => $character['name'] ?? null,
            'race' => $character['race'] ?? null,
            'gender' => $character['gender'] ?? null,
            'profession' => $character['profession'] ?? null,
            'level' => $character['level'] ?? null,
            'age' => $character['age'] ?? null,
            'deaths' => $character['deaths'] ?? null,
            'created' => $character['created'] ?? null,
            'guild' => $guild,
            'crafting' => array_map(fn (array $c) => [
                'discipline' => $c['discipline'],
                'rating' => $c['rating'],
                'active' => $c['active'] ?? false,
            ], $character['crafting'] ?? []),
            'specializations' => array_values(array_map(fn (int $id) => [
                'id' => $id,
                'name' => $specs[$id]['name'] ?? null,
                'elite' => $specs[$id]['elite'] ?? false,
                'icon' => $specs[$id]['icon'] ?? null,
            ], $specIds)),
            'equipment' => $this->formatter->formatSlots($equipped),
            'bags' => [
                'count' => count(array_filter($character['bags'] ?? [])),
                'slots' => $bagSlots,
                'used' => $usedSlots,
            ],
        ];
    }
}
