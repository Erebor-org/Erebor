<?php

namespace App\Service\Gw2;

/**
 * Expands a legendary's recipe tree against what the account owns. Pure logic, no API call.
 *
 * Every node takes what it can from the stock (an owned intermediate covers its whole branch),
 * then the missing part is either bought (instant buy, lowest sell listing) or crafted from its
 * children, whichever is cheaper. What can be neither bought nor crafted (Gift of Exploration,
 * currencies...) is an objective: it counts apart from the gold cost.
 */
class Gw2LegendaryPlanner
{
    public const OWNED = 'owned';
    public const BUY = 'buy';
    public const CRAFT = 'craft';
    public const OBJECTIVE = 'objective';

    // Guard against a malformed recipe file referencing itself
    private const MAX_DEPTH = 12;
    // Currency 1 is coin (copper): what a merchant asks in gold is a cost, not an objective
    private const COIN = 1;

    private array $storable;

    /**
     * @param array<int, int> $buyPrices        item id => instant buy unit price (lowest sell listing), tradable items only
     * @param int[]           $storableMaterials items the material storage accepts: common materials, never collection pieces
     */
    public function __construct(
        private Gw2RecipeBook $recipes,
        private array $buyPrices,
        array $storableMaterials = [],
    ) {
        $this->storable = array_flip($storableMaterials);
    }

    /**
     * @param array<int, int> $items      item id => owned count; consumed as the tree uses it
     * @param array<int, int> $currencies currency id => owned amount; consumed too
     */
    public function plan(int $legendaryId, array &$items, array &$currencies): array
    {
        // The legendary itself is crafted, never "bought" even if it is on the trading post
        return $this->node('item', $legendaryId, 1, $items, $currencies, 0, true);
    }

    /** Remaining gold cost and objectives, plus progress compared to starting from nothing */
    public function summarize(array $plan, array $fromScratch): array
    {
        $baseline = $fromScratch['cost'];
        $resources = $baseline > 0 ? max(0.0, min(1.0, 1 - $plan['cost'] / $baseline)) : 1.0;
        $objectivesTotal = $fromScratch['objectives'];
        $objectivesDone = max(0, $objectivesTotal - $plan['objectives']);

        // Objectives weigh like the resources in the overall progress, each the same
        $progress = $objectivesTotal > 0
            ? ($resources + $objectivesDone / $objectivesTotal) / 2
            : $resources;

        return [
            'cost' => $plan['cost'],
            'costFromScratch' => $baseline,
            'resourcesProgress' => round($resources, 4),
            'objectives' => ['done' => $objectivesDone, 'total' => $objectivesTotal],
            'progress' => round($plan['missing'] > 0 ? $progress : 1.0, 4),
        ];
    }

    /**
     * Flat list of what the plan uses or still needs, one row per item/currency: the leaves of
     * the plan (owned branches, bought items, objectives).
     */
    public function shoppingList(array $plan): array
    {
        $rows = [];
        $walk = function (array $node) use (&$walk, &$rows) {
            $isLeaf = !$node['children'] || $node['childrenIgnored'] || self::OWNED === $node['decision'];
            if ($isLeaf) {
                $key = $node['type'].':'.$node['id'];
                $row = $rows[$key] ?? [
                    'type' => $node['type'], 'id' => $node['id'],
                    'needed' => 0, 'owned' => 0, 'missing' => 0,
                    'unitPrice' => $node['buyUnit'], 'cost' => 0, 'decision' => $node['decision'],
                ];
                $row['needed'] += $node['count'];
                $row['owned'] += $node['owned'];
                $row['missing'] += $node['missing'];
                $row['cost'] += self::BUY === $node['decision'] ? $node['cost'] : 0;
                if ($node['missing'] > 0) {
                    $row['decision'] = $node['decision'];
                }
                $rows[$key] = $row;
            }
            if (!$isLeaf) {
                foreach ($node['children'] as $child) {
                    $walk($child);
                }
            }
        };
        $walk($plan);

        return array_values($rows);
    }

    private function node(string $type, int $id, int $count, array &$items, array &$currencies, int $depth, bool $isRoot = false): array
    {
        if ('currency' === $type && self::COIN === $id) {
            return [
                'type' => $type, 'id' => $id, 'count' => $count, 'owned' => 0, 'missing' => $count,
                'source' => null, 'buyUnit' => 1, 'buyCost' => $count, 'craftCost' => null,
                'cost' => $count, 'objectives' => 0, 'decision' => self::BUY,
                'childrenIgnored' => false, 'startedInBranch' => false, 'children' => [],
            ];
        }

        $stock = 'currency' === $type ? $currencies : $items;
        $owned = min($stock[$id] ?? 0, $count);
        if ('currency' === $type) {
            $currencies[$id] = ($currencies[$id] ?? 0) - $owned;
        } else {
            $items[$id] = ($items[$id] ?? 0) - $owned;
        }
        $missing = $count - $owned;

        $buyUnit = 'item' === $type && !$isRoot ? ($this->buyPrices[$id] ?? null) : null;
        $node = [
            'type' => $type,
            'id' => $id,
            'count' => $count,
            'owned' => $owned,
            'missing' => $missing,
            'source' => null,
            'buyUnit' => $buyUnit,
            'buyCost' => null !== $buyUnit ? $buyUnit * $missing : null,
            'craftCost' => null,
            'cost' => 0,
            'objectives' => 0,
            'decision' => self::OWNED,
            'childrenIgnored' => false,
            // A collection piece of this branch is already owned (a precursor collection is under
            // way): an item earned in game only, not a material. Currencies don't count either.
            'startedInBranch' => $owned > 0 && 'item' === $type && null === $buyUnit && !$isRoot && !isset($this->storable[$id]),
            'children' => [],
        ];
        if (0 === $missing) {
            return $node;
        }

        $recipe = 'item' === $type && $depth < self::MAX_DEPTH ? $this->recipes->recipe($id) : null;
        if (!$recipe) {
            if (null !== $buyUnit) {
                return ['decision' => self::BUY, 'cost' => $node['buyCost']] + $node;
            }

            return ['decision' => self::OBJECTIVE, 'objectives' => 1] + $node;
        }

        // Expand on a copy of the stock: if buying wins, the children must not have used it
        $itemsAfter = $items;
        $currenciesAfter = $currencies;
        $crafts = (int) ceil($missing / max(1, $recipe['output']));
        foreach ($recipe['ingredients'] as $ingredient) {
            $node['children'][] = $this->node($ingredient['type'], $ingredient['id'], $ingredient['count'] * $crafts, $itemsAfter, $currenciesAfter, $depth + 1);
        }
        $node['source'] = $recipe['source'];
        $node['craftCost'] = array_sum(array_column($node['children'], 'cost'));
        $craftObjectives = array_sum(array_column($node['children'], 'objectives'));
        $node['startedInBranch'] = $node['startedInBranch'] || in_array(true, array_column($node['children'], 'startedInBranch'), true);

        // Buy when strictly cheaper (on a tie crafting puts the owned stock to use), or for a
        // precursor (regular crafting, unlike the Mystic Forge gifts and legendaries) whose
        // collections aren't even started: its objectives would stand between you and the legendary
        $craftNotStarted = 'crafting' === $recipe['source'] && $craftObjectives > 0 && !$node['startedInBranch'];
        if (null !== $buyUnit && ($node['buyCost'] < $node['craftCost'] || $craftNotStarted)) {
            $node['decision'] = self::BUY;
            $node['cost'] = $node['buyCost'];
            $node['childrenIgnored'] = true;

            return $node;
        }

        $items = $itemsAfter;
        $currencies = $currenciesAfter;
        $node['decision'] = self::CRAFT;
        $node['cost'] = $node['craftCost'];
        $node['objectives'] = $craftObjectives;

        return $node;
    }
}
