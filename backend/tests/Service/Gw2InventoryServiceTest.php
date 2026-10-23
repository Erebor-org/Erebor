<?php

namespace App\Tests\Service;

use App\Service\Gw2\Gw2ApiClient;
use App\Service\Gw2\Gw2InventoryService;
use App\Service\Gw2\Gw2ItemFormatter;
use App\Service\Gw2\Gw2SellAdvisor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Assembly of bags, shared slots and bank with prices and advice, every GW2 answer simulated.
 */
class Gw2InventoryServiceTest extends TestCase
{
    private array $requestedPaths = [];
    private ?Gw2InventoryService $service = null;

    private function build(bool $refresh = false): array
    {
        if ($this->service) {
            return $this->service->build($refresh);
        }

        $http = new MockHttpClient(function (string $method, string $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $this->requestedPaths[] = $path;
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $ids = array_map('intval', explode(',', $query['ids'] ?? ''));

            $items = [
                1 => ['id' => 1, 'name' => 'Masse éveillée', 'type' => 'Weapon', 'rarity' => 'Rare', 'level' => 80, 'vendor_value' => 198, 'flags' => [], 'details' => ['type' => 'Mace']],
                2 => ['id' => 2, 'name' => 'Os effrité', 'type' => 'Trophy', 'rarity' => 'Junk', 'level' => 0, 'vendor_value' => 9, 'flags' => ['NoSalvage']],
                19721 => ['id' => 19721, 'name' => "Boule d'ectoplasme", 'type' => 'Trophy', 'rarity' => 'Exotic', 'level' => 0, 'vendor_value' => 256, 'flags' => []],
            ];
            $prices = [
                1 => ['id' => 1, 'buys' => ['unit_price' => 1234], 'sells' => ['unit_price' => 1620]],
                19721 => ['id' => 19721, 'buys' => ['unit_price' => 1600], 'sells' => ['unit_price' => 1650]],
            ];

            $body = match ($path) {
                '/v2/characters' => ['Siisko Siam'],
                '/v2/characters/Siisko%20Siam/inventory' => ['bags' => [
                    ['size' => 4, 'inventory' => [['id' => 2, 'count' => 20], null, ['id' => 19721, 'count' => 3], null]],
                    null,
                ]],
                '/v2/account/inventory' => [null, null],
                '/v2/account/bank' => [['id' => 1, 'count' => 1], null, null],
                '/v2/items' => array_values(array_intersect_key($items, array_flip($ids))),
                '/v2/itemstats' => [],
                '/v2/commerce/prices' => array_values(array_intersect_key($prices, array_flip($ids))),
                '/v2/materials' => [['id' => 6, 'items' => [19721]]],
            };

            return new MockResponse(json_encode($body));
        });

        $client = new Gw2ApiClient($http, new ArrayAdapter(), 'TEST-KEY');

        $this->service = new Gw2InventoryService($client, new Gw2ItemFormatter($client), new Gw2SellAdvisor());

        return $this->service->build($refresh);
    }

    private function countRequests(string $path): int
    {
        return count(array_keys($this->requestedPaths, $path, true));
    }

    public function testCollectsEverySlotWithItsLocationAndAdvice(): void
    {
        $result = $this->build();
        $byName = array_column($result['slots'], null, 'name');

        $this->assertCount(3, $result['slots']); // empty slots are skipped
        $this->assertSame(['location' => 'inventory', 'character' => 'Siisko Siam'], array_intersect_key($byName['Os effrité'], ['location' => 1, 'character' => 1]));
        $this->assertSame('bank', $byName['Masse éveillée']['location']);
        $this->assertNull($byName['Masse éveillée']['character']);

        $this->assertSame(Gw2SellAdvisor::VENDOR, $byName['Os effrité']['advice']['action']);
        $this->assertSame(180, $byName['Os effrité']['advice']['total']);
        $this->assertSame(Gw2SellAdvisor::KEEP, $byName["Boule d'ectoplasme"]['advice']['action']); // storable material
        // 0.875 * (1600 - 80 - 160) = 1190 > 1234 - 62 - 123 = 1049
        $this->assertSame(Gw2SellAdvisor::SALVAGE, $byName['Masse éveillée']['advice']['action']);
        $this->assertSame(['buy' => 1234, 'sell' => 1620], $byName['Masse éveillée']['price']);
        $this->assertNull($byName['Os effrité']['price']);
    }

    public function testReportsCapacitiesAndEctoPrice(): void
    {
        $result = $this->build();

        $this->assertSame([['name' => 'Siisko Siam', 'slots' => 4, 'used' => 2]], $result['characters']);
        $this->assertSame(['slots' => 2, 'used' => 0], $result['shared']);
        $this->assertSame(['slots' => 3, 'used' => 1], $result['bank']);
        $this->assertSame(['buy' => 1600, 'net' => 1360], $result['ecto']);
    }

    public function testPricesAreFetchedInOneBatch(): void
    {
        $this->build();

        $this->assertSame(1, $this->countRequests('/v2/commerce/prices'));
        $this->assertSame(1, $this->countRequests('/v2/items'));
    }

    public function testSecondBuildIsServedFromCache(): void
    {
        $this->build();
        $this->build();

        $this->assertSame(1, $this->countRequests('/v2/account/bank'));
        $this->assertSame(1, $this->countRequests('/v2/commerce/prices'));
    }

    public function testRefreshReadsBagsBankAndPricesAgainButNotStaticData(): void
    {
        $this->build();
        $this->build(true);

        foreach (['/v2/characters', '/v2/characters/Siisko%20Siam/inventory', '/v2/account/inventory', '/v2/account/bank', '/v2/commerce/prices'] as $path) {
            $this->assertSame(2, $this->countRequests($path), $path);
        }
        // Item names and the material list don't change: still cached
        $this->assertSame(1, $this->countRequests('/v2/items'));
        $this->assertSame(1, $this->countRequests('/v2/materials'));
    }
}
