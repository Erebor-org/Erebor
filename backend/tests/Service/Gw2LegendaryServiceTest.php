<?php

namespace App\Tests\Service;

use App\Service\Gw2\Gw2AccountStock;
use App\Service\Gw2\Gw2ApiClient;
use App\Service\Gw2\Gw2LegendaryPlanner;
use App\Service\Gw2\Gw2LegendaryService;
use App\Service\Gw2\Gw2RecipeBook;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Twilight (30704) and Sunrise (30703) both need 250 ectos; the account owns 300, split between
 * the bank and the material storage. Every GW2 answer is simulated.
 */
class Gw2LegendaryServiceTest extends TestCase
{
    private const TWILIGHT = 30704;
    private const SUNRISE = 30703;
    private const ECTO = 19721;
    private const EXPLORATION = 19677;

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/gw2_legendary_'.uniqid();
        mkdir($this->dir);
        $recipe = fn () => ['source' => 'mystic_forge', 'output' => 1, 'ingredients' => [
            ['type' => 'item', 'id' => self::ECTO, 'count' => 250],
            ['type' => 'item', 'id' => self::EXPLORATION, 'count' => 1],
        ]];
        file_put_contents($this->dir.'/legendary_recipes.json', json_encode(['recipes' => [
            self::TWILIGHT => $recipe(),
            self::SUNRISE => $recipe(),
        ]]));
        file_put_contents($this->dir.'/legendary_sources.json', json_encode(['items' => [
            self::EXPLORATION => ['hint' => 'Exploration complète du monde'],
        ]]));
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*'));
        rmdir($this->dir);
    }

    private function service(): Gw2LegendaryService
    {
        $http = new MockHttpClient(function (string $method, string $url) {
            $path = parse_url($url, PHP_URL_PATH);
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $ids = array_map('intval', explode(',', $query['ids'] ?? ''));
            $known = fn (array $byId) => array_values(array_intersect_key($byId, array_flip($ids)));

            $body = match ($path) {
                '/v2/characters' => ['Siisko Siam'],
                '/v2/characters/Siisko%20Siam/inventory' => ['bags' => [['size' => 2, 'inventory' => [['id' => self::EXPLORATION, 'count' => 1], null]]]],
                '/v2/account/inventory' => [],
                '/v2/account/bank' => [['id' => self::ECTO, 'count' => 100]],
                '/v2/account/materials' => [['id' => self::ECTO, 'category' => 6, 'count' => 200]],
                '/v2/account/wallet' => [['id' => 1, 'value' => 5000]],
                '/v2/account/legendaryarmory' => [['id' => self::SUNRISE, 'count' => 1]],
                '/v2/materials' => [['id' => 6, 'items' => [self::ECTO]]],
                '/v2/commerce/prices' => $known([
                    self::ECTO => ['id' => self::ECTO, 'buys' => ['unit_price' => 1500], 'sells' => ['unit_price' => 1600]],
                    self::TWILIGHT => ['id' => self::TWILIGHT, 'buys' => ['unit_price' => 2000000], 'sells' => ['unit_price' => 2500000]],
                ]),
                '/v2/items' => $known([
                    self::TWILIGHT => ['id' => self::TWILIGHT, 'name' => 'Crépuscule', 'rarity' => 'Legendary', 'type' => 'Weapon', 'details' => ['type' => 'Greatsword']],
                    self::SUNRISE => ['id' => self::SUNRISE, 'name' => 'Aurore', 'rarity' => 'Legendary', 'type' => 'Weapon', 'details' => ['type' => 'Greatsword']],
                    self::ECTO => ['id' => self::ECTO, 'name' => "Boule d'ectoplasme", 'rarity' => 'Exotic', 'type' => 'Trophy'],
                    self::EXPLORATION => ['id' => self::EXPLORATION, 'name' => "Don d'exploration", 'rarity' => 'Legendary', 'type' => 'Trophy'],
                ]),
            };

            return new MockResponse(json_encode($body));
        });
        $client = new Gw2ApiClient($http, new ArrayAdapter(), 'TEST-KEY');

        return new Gw2LegendaryService($client, new Gw2AccountStock($client), new Gw2RecipeBook($this->dir));
    }

    public function testCatalogShowsEachLegendaryAgainstTheWholeStock(): void
    {
        $result = $this->service()->catalog([self::TWILIGHT]);
        $byId = array_column($result['legendaries'], null, 'id');

        $twilight = $byId[self::TWILIGHT];
        $this->assertTrue($twilight['tracked']);
        $this->assertFalse($twilight['unlocked']);
        $this->assertSame(1, $twilight['generation']);
        $this->assertSame(0, $twilight['cost']); // 300 ectos owned, 250 needed
        $this->assertSame(['done' => 1, 'total' => 1], $twilight['objectives']);
        $this->assertSame(2500000, $twilight['tradingPostPrice']);
        $this->assertTrue($byId[self::SUNRISE]['unlocked']);
        $this->assertSame('Crépuscule', $result['items']->{self::TWILIGHT}['name']);
    }

    public function testTrackedLegendariesReportSharedResources(): void
    {
        $result = $this->service()->tracked([self::TWILIGHT, self::SUNRISE]);

        $conflicts = array_column($result['conflicts'], null, 'id');
        $this->assertSame([self::ECTO, self::EXPLORATION], array_keys($conflicts));
        $this->assertSame(300, $conflicts[self::ECTO]['owned']);
        $this->assertSame([self::TWILIGHT => 250, self::SUNRISE => 250], $conflicts[self::ECTO]['usedBy']);
        // Gift of Exploration: one owned, both count on it
        $this->assertSame([self::TWILIGHT => 1, self::SUNRISE => 1], $conflicts[self::EXPLORATION]['usedBy']);
        $this->assertSame([self::SUNRISE], $result['legendaries'][0]['sharesWith']);
        $this->assertSame([self::TWILIGHT], $result['legendaries'][1]['sharesWith']);
    }

    public function testCombinedViewSharesTheStockInTrackingOrder(): void
    {
        $result = $this->service()->combined([self::TWILIGHT, self::SUNRISE]);
        [$first, $second] = $result['legendaries'];

        $this->assertSame(0, $first['cost']); // served first: 250 of the 300 ectos
        $this->assertSame(200 * 1600, $second['cost']); // 50 left, 200 to buy
        $this->assertSame(['done' => 0, 'total' => 1], $second['objectives']); // the only gift went to Twilight
        $this->assertSame(320000, $result['totalCost']);

        $rows = array_column($result['shoppingList'], null, 'id');
        $this->assertSame(['needed' => 500, 'owned' => 300, 'missing' => 200], array_intersect_key($rows[self::ECTO], array_flip(['needed', 'owned', 'missing'])));
        $this->assertSame(['bank' => 100, 'materials' => 200], $rows[self::ECTO]['locations']);
    }

    public function testDetailSplitsShoppingListAndObjectives(): void
    {
        $result = $this->service()->detail(self::SUNRISE);

        $this->assertSame(self::SUNRISE, $result['tree']['id']);
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $result['tree']['decision']); // never "buy the legendary"
        $this->assertSame([self::ECTO, self::EXPLORATION], array_column($result['shoppingList'], 'id')); // gift owned: listed, not an objective
        $this->assertSame([], $result['objectives']);

        // How to get each resource of the tree
        $ecto = $result['howTo']['item:'.self::ECTO];
        $this->assertSame(['buy' => 1600, 'sell' => 1500], $ecto['tradingPost']);
        $this->assertNull($ecto['recipe']);
        $this->assertTrue($ecto['storable']);
        $this->assertSame(['bank' => 100, 'materials' => 200], $ecto['locations']);
        $exploration = $result['howTo']['item:'.self::EXPLORATION];
        $this->assertNull($exploration['tradingPost']);
        $this->assertSame('Exploration complète du monde', $exploration['hint']);
        $this->assertSame('mystic_forge', $result['howTo']['item:'.self::SUNRISE]['recipe']['source']);
    }
}
