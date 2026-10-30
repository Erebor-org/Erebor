<?php

namespace App\Tests\Controller;

use App\Controller\Gw2Controller;
use App\Service\Gw2\Gw2ApiClient;
use App\Service\Gw2\Gw2ItemFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Character sheet formatting (formatCharacter), with every GW2 API answer simulated.
 * Called through reflection to bypass AbstractController::json()'s container dependency.
 */
class Gw2ControllerTest extends TestCase
{
    private const ITEMS = [
        // Fixed stats: read from the item's infix_upgrade
        64556 => ['id' => 64556, 'name' => 'Manteau rubané', 'type' => 'Armor', 'rarity' => 'Rare', 'level' => 55, 'icon' => 'coat.png', 'description' => '',
            'details' => ['type' => 'Coat', 'weight_class' => 'Heavy', 'defense' => 193,
                'infix_upgrade' => ['id' => 152, 'attributes' => [['attribute' => 'Power', 'modifier' => 70], ['attribute' => 'CritDamage', 'modifier' => 50]]]]],
        // Selectable stats: chosen on the equipment entry, not on the item
        74155 => ['id' => 74155, 'name' => 'Épée élevée', 'type' => 'Weapon', 'rarity' => 'Ascended', 'level' => 80, 'icon' => 'sword.png',
            'description' => '<c=@flavor>Forgée dans la brume.</c><br>Deuxième ligne',
            'details' => ['type' => 'Sword', 'min_power' => 905, 'max_power' => 1000, 'defense' => 0]],
        24796 => ['id' => 24796, 'name' => 'Rune de la Légion de la Flamme', 'type' => 'UpgradeComponent', 'rarity' => 'Exotic', 'level' => 60,
            'details' => ['type' => 'Rune', 'bonuses' => ['Puissance +15', 'Durée de Brûlure +6%']]],
        24618 => ['id' => 24618, 'name' => "Cachet d'exactitude", 'type' => 'UpgradeComponent', 'rarity' => 'Exotic', 'level' => 60,
            'details' => ['type' => 'Sigil', 'infix_upgrade' => ['id' => 264, 'buff' => ['description' => 'Chance de coup critique +7%'], 'attributes' => []]]],
        49432 => ['id' => 49432, 'name' => 'Infusion puissante', 'type' => 'UpgradeComponent', 'rarity' => 'Exotic', 'level' => 0,
            'details' => ['type' => 'Default', 'infix_upgrade' => ['id' => 0, 'attributes' => [['attribute' => 'Power', 'modifier' => 5]]]]],
    ];

    private function makeController(): Gw2Controller
    {
        $http = new MockHttpClient(function (string $method, string $url) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $ids = array_map('intval', explode(',', $query['ids'] ?? ''));
            $path = parse_url($url, PHP_URL_PATH);

            $body = match ($path) {
                '/v2/items' => array_values(array_intersect_key(self::ITEMS, array_flip($ids))),
                '/v2/itemstats' => array_values(array_intersect_key([
                    152 => ['id' => 152, 'name' => 'aiguisé'],
                    1220 => ['id' => 1220, 'name' => 'berserker'],
                ], array_flip($ids))),
                '/v2/specializations' => [['id' => 42, 'name' => 'Zèle', 'elite' => false, 'icon' => 'zeal.png']],
            };

            return new MockResponse(json_encode($body));
        });

        $client = new Gw2ApiClient($http, new ArrayAdapter(), 'TEST-KEY');

        return new Gw2Controller($client, new Gw2ItemFormatter($client));
    }

    private function format(array $character): array
    {
        $method = new \ReflectionMethod(Gw2Controller::class, 'formatCharacter');
        $method->setAccessible(true);

        return $method->invoke($this->makeController(), $character);
    }

    private function character(): array
    {
        return [
            'name' => 'Siisko Siam', 'race' => 'Norn', 'gender' => 'Female', 'profession' => 'Guardian', 'level' => 80,
            'age' => 3600, 'deaths' => 2, 'created' => '2026-10-18T09:00:00Z', 'guild' => null,
            'crafting' => [['discipline' => 'Weaponsmith', 'rating' => 10, 'active' => true]],
            'build_tabs' => [['is_active' => true, 'build' => ['specializations' => [['id' => 42], ['id' => null]]]]],
            'bags' => [['size' => 20, 'inventory' => [['id' => 1], null, ['id' => 2]]], null],
            'active_equipment_tab' => 1,
            'equipment' => [
                ['id' => 64556, 'slot' => 'Coat', 'upgrades' => [24796], 'binding' => 'Character', 'location' => 'Equipped', 'tabs' => [1]],
                ['id' => 74155, 'slot' => 'WeaponA1', 'upgrades' => [24618], 'infusions' => [49432], 'location' => 'Equipped', 'tabs' => [1],
                    'stats' => ['id' => 1220, 'attributes' => ['Power' => 251, 'Precision' => 179]]],
                // Other equipment tab and armory storage: not worn right now
                ['id' => 64556, 'slot' => 'Helm', 'location' => 'Equipped', 'tabs' => [2]],
                ['id' => 74155, 'slot' => 'WeaponB1', 'location' => 'LegendaryArmory', 'tabs' => [1]],
            ],
        ];
    }

    public function testEquipmentWithFixedStatsAndRune(): void
    {
        $sheet = $this->format($this->character());

        $this->assertSame(['Coat', 'WeaponA1'], array_column($sheet['equipment'], 'slot'));

        $coat = $sheet['equipment'][0];
        $this->assertSame('Manteau rubané', $coat['name']);
        $this->assertSame('Armor', $coat['itemType']);
        $this->assertSame('Heavy', $coat['weightClass']);
        $this->assertSame(193, $coat['defense']);
        $this->assertNull($coat['power']);
        $this->assertSame('aiguisé', $coat['statsName']);
        $this->assertSame([['attribute' => 'Power', 'modifier' => 70], ['attribute' => 'CritDamage', 'modifier' => 50]], $coat['attributes']);
        $this->assertSame('Rune de la Légion de la Flamme', $coat['upgrades'][0]['name']);
        $this->assertSame(['Puissance +15', 'Durée de Brûlure +6%'], $coat['upgrades'][0]['bonuses']);
        $this->assertSame('Character', $coat['binding']);
    }

    public function testEquipmentWithSelectableStatsSigilAndInfusion(): void
    {
        $weapon = $this->format($this->character())['equipment'][1];

        $this->assertSame([905, 1000], $weapon['power']);
        $this->assertNull($weapon['defense']);
        $this->assertSame('berserker', $weapon['statsName']);
        $this->assertSame([['attribute' => 'Power', 'modifier' => 251], ['attribute' => 'Precision', 'modifier' => 179]], $weapon['attributes']);
        $this->assertSame(['Chance de coup critique +7%'], $weapon['upgrades'][0]['bonuses']);
        $this->assertSame('Infusion puissante', $weapon['infusions'][0]['name']);
        $this->assertSame([['attribute' => 'Power', 'modifier' => 5]], $weapon['infusions'][0]['attributes']);
        $this->assertSame("Forgée dans la brume.\nDeuxième ligne", $weapon['description']); // GW2 markup stripped
    }

    public function testCharacterSummary(): void
    {
        $sheet = $this->format($this->character());

        $this->assertSame(['count' => 1, 'slots' => 20, 'used' => 2], $sheet['bags']);
        $this->assertSame([['id' => 42, 'name' => 'Zèle', 'elite' => false, 'icon' => 'zeal.png']], $sheet['specializations']);
        $this->assertNull($sheet['guild']);
    }
}
