<?php

namespace App\Tests\Service;

use App\Service\Gw2\Gw2LegendaryPlanner;
use App\Service\Gw2\Gw2RecipeBook;
use PHPUnit\Framework\TestCase;

/**
 * A small fake legendary tree:
 *
 *   LEGENDARY (1000) = PRECURSOR (1001) + GIFT (1002) + 2 x EXPLORATION (1003, objective)
 *   PRECURSOR        = 10 x INGOT (2001)                       tradable 5000
 *   GIFT             = 250 x FANG (3001) + BLOODSTONE (1004)  not tradable
 *   INGOT            = 2 x ORE (3002)                          tradable 30
 *   BLOODSTONE       = 200 spirit shards (currency 23), bought from a merchant
 *
 * plus, for the precursor rules: COLLECTOR (1005) = PRECURSOR alternative needing a collection
 * piece (4001, untradable) and RUNESTONE (1006) sold 1 gold by a merchant.
 */
class Gw2LegendaryPlannerTest extends TestCase
{
    private const LEGENDARY = 1000;
    private const PRECURSOR = 1001;
    private const GIFT = 1002;
    private const EXPLORATION = 1003;
    private const BLOODSTONE = 1004;
    private const INGOT = 2001;
    private const FANG = 3001;
    private const ORE = 3002;
    private const SPIRIT_SHARDS = 23;
    private const COLLECTOR = 1005;
    private const RUNESTONE = 1006;
    private const COLLECTION_PIECE = 4001;
    private const COIN = 1;

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/gw2_planner_'.uniqid();
        mkdir($this->dir);
        $item = fn (int $id, int $count) => ['type' => 'item', 'id' => $id, 'count' => $count];
        file_put_contents($this->dir.'/legendary_recipes.json', json_encode(['recipes' => [
            self::LEGENDARY => ['source' => 'mystic_forge', 'output' => 1, 'ingredients' => [$item(self::PRECURSOR, 1), $item(self::GIFT, 1), $item(self::EXPLORATION, 2)]],
            self::PRECURSOR => ['source' => 'crafting', 'output' => 1, 'ingredients' => [$item(self::INGOT, 10)]],
            self::GIFT => ['source' => 'mystic_forge', 'output' => 1, 'ingredients' => [$item(self::FANG, 250), $item(self::BLOODSTONE, 1)]],
            self::INGOT => ['source' => 'crafting', 'output' => 1, 'ingredients' => [$item(self::ORE, 2)]],
            // A precursor crafted from a collection piece, also sold on the trading post
            self::COLLECTOR => ['source' => 'crafting', 'output' => 1, 'ingredients' => [$item(self::COLLECTION_PIECE, 1), $item(self::FANG, 1)]],
        ]]));
        file_put_contents($this->dir.'/legendary_sources.json', json_encode(['items' => [
            self::BLOODSTONE => ['vendor' => ['ingredients' => [['type' => 'currency', 'id' => self::SPIRIT_SHARDS, 'count' => 200]]]],
            self::EXPLORATION => ['hint' => 'Exploration complète du monde'],
            self::RUNESTONE => ['vendor' => ['ingredients' => [['type' => 'currency', 'id' => self::COIN, 'count' => 10000]]]],
        ]]));
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*'));
        rmdir($this->dir);
    }

    private function planner(array $prices = []): Gw2LegendaryPlanner
    {
        return new Gw2LegendaryPlanner(new Gw2RecipeBook($this->dir), $prices + [
            self::LEGENDARY => 900000, // tradable, but the legendary itself is never "bought"
            self::PRECURSOR => 5000,
            self::INGOT => 100,
            self::ORE => 30,
            self::FANG => 40,
        ]);
    }

    private function plan(array $items = [], array $currencies = [], array $prices = []): array
    {
        return $this->planner($prices)->plan(self::LEGENDARY, $items, $currencies);
    }

    private function child(array $node, int $id): array
    {
        foreach ($node['children'] as $child) {
            if ($child['id'] === $id) {
                return $child;
            }
        }
        $this->fail("No child $id");
    }

    public function testFromScratchEverythingIsCraftedOrBoughtAtTheCheapest(): void
    {
        $plan = $this->plan();

        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $plan['decision']);
        // Precursor: craft = 10 ingots * min(100 buy, 2*30 craft) = 600 < 5000 buy
        $precursor = $this->child($plan, self::PRECURSOR);
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $precursor['decision']);
        $this->assertSame(600, $precursor['cost']);
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $this->child($precursor, self::INGOT)['decision']);
        // Gift: 250 fangs bought, bloodstone needs spirit shards (objective)
        $gift = $this->child($plan, self::GIFT);
        $this->assertSame(10000, $gift['cost']);
        $this->assertSame(1, $gift['objectives']);
        // Total: 600 + 10000, objectives: spirit shards + 2 gifts of exploration
        $this->assertSame(10600, $plan['cost']);
        $this->assertSame(2, $plan['objectives']);
    }

    public function testOwnedIntermediateCoversItsWholeBranch(): void
    {
        $plan = $this->plan([self::GIFT => 1, self::FANG => 250]);

        $gift = $this->child($plan, self::GIFT);
        $this->assertSame(Gw2LegendaryPlanner::OWNED, $gift['decision']);
        $this->assertSame([], $gift['children']);
        $this->assertSame(600, $plan['cost']);
    }

    public function testStockIsConsumedOnce(): void
    {
        $items = [self::ORE => 15];
        $currencies = [];
        $plan = $this->planner()->plan(self::LEGENDARY, $items, $currencies);

        // 10 ingots need 20 ore: the 15 owned are used, the 5 missing are bought
        $precursor = $this->child($plan, self::PRECURSOR);
        $ingots = $this->child($precursor, self::INGOT);
        $this->assertSame(15, $this->child($ingots, self::ORE)['owned']);
        $this->assertSame(5 * 30, $ingots['cost']);
        $this->assertSame(0, $items[self::ORE]); // all used, nothing counted twice
    }

    public function testBuyingWinsWhenCheaperAndLeavesTheStockUntouched(): void
    {
        $items = [self::ORE => 10];
        $currencies = [];
        // Precursor on sale for 100: crafting would still cost 10 ingots at 1000 (bought, or crafted
        // from 2 ore at 1000 with half the ore owned: the same price)
        $plan = $this->planner([self::PRECURSOR => 100, self::INGOT => 1000, self::ORE => 1000])
            ->plan(self::LEGENDARY, $items, $currencies);

        $precursor = $this->child($plan, self::PRECURSOR);
        $this->assertSame(10000, $precursor['craftCost']);
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $this->child($precursor, self::INGOT)['decision']); // tie: craft
        $this->assertSame(Gw2LegendaryPlanner::BUY, $precursor['decision']);
        $this->assertSame(100, $precursor['cost']);
        $this->assertTrue($precursor['childrenIgnored']);
        $this->assertSame(10, $items[self::ORE]); // not crafted, so the ore stays available
    }

    public function testCurrenciesComeFromTheWallet(): void
    {
        $plan = $this->plan([self::EXPLORATION => 2], [self::SPIRIT_SHARDS => 500]);

        $bloodstone = $this->child($this->child($plan, self::GIFT), self::BLOODSTONE);
        $this->assertSame('vendor', $bloodstone['source']);
        $this->assertSame(200, $bloodstone['children'][0]['owned']);
        $this->assertSame(0, $plan['objectives']);
    }

    public function testSummaryProgressMixesGoldAndObjectives(): void
    {
        $planner = $this->planner();
        $none = [];
        $noCurrencies = [];
        $fromScratch = $planner->plan(self::LEGENDARY, $none, $noCurrencies);

        $items = [self::FANG => 250, self::EXPLORATION => 2];
        $currencies = [];
        $summary = $planner->summarize($planner->plan(self::LEGENDARY, $items, $currencies), $fromScratch);

        $this->assertSame(600, $summary['cost']);
        $this->assertSame(10600, $summary['costFromScratch']);
        $this->assertSame(['done' => 1, 'total' => 2], $summary['objectives']);
        // resources: 1 - 600/10600 = 0.9434; objectives 1/2; average
        $this->assertEqualsWithDelta((0.9434 + 0.5) / 2, $summary['progress'], 0.001);
    }

    public function testShoppingListAggregatesTheLeaves(): void
    {
        $planner = $this->planner();
        $items = [self::FANG => 100];
        $currencies = [self::SPIRIT_SHARDS => 50];
        $rows = array_column(
            array_map(fn ($r) => $r + ['key' => $r['type'].':'.$r['id']], $planner->shoppingList($planner->plan(self::LEGENDARY, $items, $currencies))),
            null,
            'key',
        );

        $this->assertSame(['needed' => 250, 'owned' => 100, 'missing' => 150, 'cost' => 6000], array_intersect_key($rows['item:'.self::FANG], array_flip(['needed', 'owned', 'missing', 'cost'])));
        $this->assertSame(Gw2LegendaryPlanner::BUY, $rows['item:'.self::FANG]['decision']);
        $this->assertSame(['needed' => 200, 'owned' => 50, 'missing' => 150], array_intersect_key($rows['currency:'.self::SPIRIT_SHARDS], array_flip(['needed', 'owned', 'missing'])));
        $this->assertSame(Gw2LegendaryPlanner::OBJECTIVE, $rows['item:'.self::EXPLORATION]['decision']);
        $this->assertSame(20, $rows['item:'.self::ORE]['needed']);
        $this->assertArrayNotHasKey('item:'.self::INGOT, $rows); // crafted, so not a leaf
    }

    public function testCoinPricesAreGoldToSpendNotObjectives(): void
    {
        $items = [];
        $currencies = [self::COIN => 999999];
        $node = (new Gw2LegendaryPlanner(new Gw2RecipeBook($this->dir), []))->plan(self::RUNESTONE, $items, $currencies);

        $this->assertSame(10000, $node['cost']);
        $this->assertSame(0, $node['objectives']);
        $this->assertSame(999999, $currencies[self::COIN]); // the wallet's gold isn't "spent" by the plan
    }

    public function testPrecursorIsBoughtWhileItsCollectionIsUntouched(): void
    {
        $planner = $this->planner([self::COLLECTOR => 900000]);
        $items = [];
        $currencies = [];
        $untouched = $planner->plan(self::COLLECTOR, $items, $currencies);
        // root is never bought: check the decision through a parent-less call on a child instead
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $untouched['decision']);

        $items = [];
        $node = $this->invokeNode($planner, self::COLLECTOR, $items);
        $this->assertSame(Gw2LegendaryPlanner::BUY, $node['decision']); // craft = 40 + 1 objective not started
        $this->assertTrue($node['childrenIgnored']);

        $items = [self::COLLECTION_PIECE => 1];
        $node = $this->invokeNode($planner, self::COLLECTOR, $items);
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $node['decision']); // collection under way: craft
        $this->assertSame(40, $node['cost']);

        $items = [self::FANG => 1]; // owning a tradable material doesn't count as "started"
        $node = $this->invokeNode($planner, self::COLLECTOR, $items);
        $this->assertSame(Gw2LegendaryPlanner::BUY, $node['decision']);

        // nor an account bound material of the material storage (here FANG, untradable)
        $withStorage = new Gw2LegendaryPlanner(new Gw2RecipeBook($this->dir), [self::COLLECTOR => 900000], [self::FANG]);
        $items = [self::FANG => 1];
        $this->assertSame(Gw2LegendaryPlanner::BUY, $this->invokeNode($withStorage, self::COLLECTOR, $items)['decision']);
    }

    public function testMysticForgeComponentsAreCraftedEvenWithUnstartedObjectives(): void
    {
        // The legendary's gift needs spirit shards (an objective) and nothing is started:
        // still crafted, the "buy while untouched" rule is for precursors only
        $items = [];
        $currencies = [];
        $gift = $this->child($this->planner([self::GIFT => 99999999])->plan(self::LEGENDARY, $items, $currencies), self::GIFT);
        $this->assertSame(Gw2LegendaryPlanner::CRAFT, $gift['decision']);
    }

    private function invokeNode(Gw2LegendaryPlanner $planner, int $id, array &$items): array
    {
        $currencies = [];
        $method = new \ReflectionMethod($planner, 'node');
        $method->setAccessible(true);

        return $method->invokeArgs($planner, ['item', $id, 1, &$items, &$currencies, 1]);
    }

    public function testFullyOwnedLegendaryIsComplete(): void
    {
        $planner = $this->planner();
        $none = [];
        $noCurrencies = [];
        $fromScratch = $planner->plan(self::LEGENDARY, $none, $noCurrencies);
        $items = [self::LEGENDARY => 1];
        $currencies = [];

        $summary = $planner->summarize($planner->plan(self::LEGENDARY, $items, $currencies), $fromScratch);

        $this->assertSame(1.0, $summary['progress']);
        $this->assertSame(0, $summary['cost']);
    }
}
