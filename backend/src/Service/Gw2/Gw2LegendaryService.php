<?php

namespace App\Service\Gw2;

/**
 * Legendary tracking: catalogue, followed legendaries, detail of one and the combined view,
 * all computed against what the account owns right now.
 *
 * Each legendary on its own counts the whole stock as available. The combined view shares the
 * stock between the followed legendaries in the order they were followed, and "conflicts" lists
 * the items several of them would use more of than the account owns.
 */
class Gw2LegendaryService
{
    public function __construct(
        private Gw2ApiClient $gw2,
        private Gw2AccountStock $accountStock,
        private Gw2RecipeBook $recipes,
    ) {
    }

    /** @param int[] $trackedIds */
    public function catalog(array $trackedIds, bool $refresh = false): array
    {
        $context = $this->context($refresh);
        $entries = [];
        foreach (Gw2LegendaryCatalog::ITEMS as $item) {
            $entries[] = $this->summary($item['id'], $context) + ['tracked' => in_array($item['id'], $trackedIds, true)];
        }

        return ['legendaries' => $entries] + $this->meta($context, array_merge(
            array_column(Gw2LegendaryCatalog::ITEMS, 'id'),
            $this->componentIds($entries),
        ));
    }

    /** @param int[] $trackedIds followed legendaries, oldest first */
    public function tracked(array $trackedIds, bool $refresh = false): array
    {
        $context = $this->context($refresh);
        $summaries = [];
        $plans = [];
        foreach ($trackedIds as $id) {
            $summaries[$id] = $this->summary($id, $context, $plans[$id]);
        }

        // Items several followed legendaries count on, beyond what the account owns
        $owned = Gw2AccountStock::totals($context['stock']['items']);
        $usage = [];
        foreach ($plans as $legendaryId => $plan) {
            foreach ($context['planner']->shoppingList($plan) as $row) {
                if ('item' === $row['type'] && $row['owned'] > 0) {
                    $usage[$row['id']][$legendaryId] = $row['owned'];
                }
            }
        }
        $conflicts = [];
        foreach ($usage as $itemId => $byLegendary) {
            if (count($byLegendary) > 1 && array_sum($byLegendary) > ($owned[$itemId] ?? 0)) {
                $conflicts[] = ['id' => $itemId, 'owned' => $owned[$itemId] ?? 0, 'usedBy' => $byLegendary];
                foreach (array_keys($byLegendary) as $legendaryId) {
                    $summaries[$legendaryId]['sharesWith'] = array_values(array_unique(array_merge(
                        $summaries[$legendaryId]['sharesWith'] ?? [],
                        array_diff(array_keys($byLegendary), [$legendaryId]),
                    )));
                }
            }
        }

        return [
            'legendaries' => array_values($summaries),
            'conflicts' => $conflicts,
        ] + $this->meta($context, array_merge($trackedIds, array_column($conflicts, 'id'), $this->componentIds($summaries)));
    }

    public function detail(int $legendaryId, bool $refresh = false): array
    {
        $context = $this->context($refresh);
        $summary = $this->summary($legendaryId, $context, $plan);
        $rows = $this->withLocations($context['planner']->shoppingList($plan), $context['stock']);
        $achievements = $this->achievementsOf($legendaryId, $context);

        $objectives = array_values(array_filter($rows, fn (array $r) => Gw2LegendaryPlanner::OBJECTIVE === $r['decision'] && $r['missing'] > 0));
        $meta = $this->meta($context, array_merge(
            $this->idsIn($plan),
            array_merge(...array_map(fn (array $a) => array_column(array_filter($a['bits'], fn ($b) => null !== $b['item']), 'item'), $achievements ?: [['bits' => []]])),
        ));
        // How to get each objective: hand-written or Le Bus Magique hint, else the item's own description
        $descriptions = $this->gw2->getItems(array_column(array_filter($objectives, fn ($o) => 'item' === $o['type']), 'id'));
        $objectives = array_map(fn (array $o) => $o + [
            'hint' => 'item' === $o['type']
                ? ($this->recipes->hint($o['id']) ?? $this->plainText($descriptions[$o['id']]['description'] ?? '') ?: null)
                : $this->recipes->currencyHint($o['id']),
            'hintSource' => 'item' === $o['type'] ? $this->recipes->hintSource($o['id']) : null,
        ], $objectives);

        return [
            'legendary' => $summary,
            'tree' => $plan,
            'shoppingList' => array_values(array_filter($rows, fn (array $r) => Gw2LegendaryPlanner::OBJECTIVE !== $r['decision'] || 0 === $r['missing'])),
            'objectives' => $objectives,
            'achievements' => $achievements,
        ] + $meta;
    }

    /** The followed legendaries sharing one stock, in the order they were followed */
    public function combined(array $trackedIds, bool $refresh = false): array
    {
        $context = $this->context($refresh);
        $items = Gw2AccountStock::totals($context['stock']['items']);
        $currencies = $context['stock']['currencies'];

        $summaries = [];
        $rows = [];
        $ids = $trackedIds;
        foreach ($trackedIds as $id) {
            $plan = $context['planner']->plan($id, $items, $currencies);
            $empty = [];
            $none = [];
            $summaries[] = $context['planner']->summarize($plan, $context['planner']->plan($id, $empty, $none))
                + ['id' => $id, 'generation' => Gw2LegendaryCatalog::generation($id)];
            foreach ($context['planner']->shoppingList($plan) as $row) {
                $key = $row['type'].':'.$row['id'];
                if (isset($rows[$key])) {
                    foreach (['needed', 'owned', 'missing', 'cost'] as $field) {
                        $rows[$key][$field] += $row[$field];
                    }
                    if ($row['missing'] > 0) {
                        $rows[$key]['decision'] = $row['decision'];
                    }
                } else {
                    $rows[$key] = $row;
                }
            }
            $ids = array_merge($ids, $this->idsIn($plan));
        }

        return [
            'legendaries' => $summaries,
            'totalCost' => array_sum(array_column($summaries, 'cost')),
            'shoppingList' => $this->withLocations(array_values($rows), $context['stock']),
        ] + $this->meta($context, $ids);
    }

    private function summary(int $legendaryId, array $context, ?array &$plan = null): array
    {
        $items = Gw2AccountStock::totals($context['stock']['items']);
        $currencies = $context['stock']['currencies'];
        $plan = $context['planner']->plan($legendaryId, $items, $currencies);
        $empty = [];
        $none = [];
        $fromScratch = $context['planner']->plan($legendaryId, $empty, $none);

        // Next step: the unfinished component of the legendary closest to done
        $next = array_filter($plan['children'], fn (array $c) => $c['missing'] > 0);
        usort($next, fn (array $a, array $b) => [$a['objectives'], $a['cost']] <=> [$b['objectives'], $b['cost']]);

        $unlocked = in_array($legendaryId, $context['stock']['unlocked'], true);
        $links = $this->recipes->achievements($legendaryId);
        $progress = fn (?int $id) => null === $id ? null : [
            'current' => $context['accountAchievements'][$id]['current'] ?? 0,
            'max' => $context['accountAchievements'][$id]['max'] ?? count($context['achievementDefs'][$id]['bits'] ?? []),
            'done' => $context['accountAchievements'][$id]['done'] ?? false,
        ];

        return [
            'id' => $legendaryId,
            'generation' => Gw2LegendaryCatalog::generation($legendaryId),
            'unlocked' => $unlocked,
            'hasRecipe' => null !== $this->recipes->recipe($legendaryId),
            'tradingPostPrice' => $context['buyPrices'][$legendaryId] ?? null,
            'nextStep' => $next ? ['type' => $next[0]['type'], 'id' => $next[0]['id']] : null,
            // In-game collections: "Arme légendaire : X" and the precursor collections
            'collection' => $progress($links['collection']),
            'precursorCollections' => [
                'done' => count(array_filter($links['precursor'], fn ($id) => $context['accountAchievements'][$id]['done'] ?? false)),
                'total' => count($links['precursor']),
            ],
            // Trinkets: the collections leading to them (Aurora II, Maître du Lac Doric...)
            'relatedCollections' => [
                'done' => count(array_filter($links['related'], fn ($id) => $context['accountAchievements'][$id]['done'] ?? false)),
                'total' => count($links['related']),
            ],
            'category' => Gw2LegendaryCatalog::category($legendaryId),
            'components' => array_map(fn (array $c) => [
                'type' => $c['type'], 'id' => $c['id'], 'missing' => $c['missing'],
                'cost' => $c['cost'], 'objectives' => $c['objectives'], 'decision' => $c['decision'],
            ], $plan['children']),
        ] + $context['planner']->summarize($plan, $fromScratch);
    }

    private function context(bool $refresh): array
    {
        $stock = $this->accountStock->load($refresh);

        $ids = array_column(Gw2LegendaryCatalog::ITEMS, 'id');
        foreach ($this->recipes->all() as $outputId => $recipe) {
            $ids[] = $outputId;
            foreach ($recipe['ingredients'] as $ingredient) {
                if ('item' === $ingredient['type']) {
                    $ids[] = $ingredient['id'];
                }
            }
        }
        $ids = array_values(array_unique($ids));
        if ($refresh) {
            $this->gw2->forgetPrices($ids);
        }

        // Buying instantly = taking the lowest sell listing
        $buyPrices = [];
        foreach ($this->gw2->getPrices($ids) as $id => $price) {
            if (($price['sells']['unit_price'] ?? 0) > 0) {
                $buyPrices[$id] = $price['sells']['unit_price'];
            }
        }

        $achievementIds = $this->recipes->allAchievementIds();
        if ($refresh) {
            $this->gw2->forgetAccountAchievements($achievementIds);
        }

        return [
            'stock' => $stock,
            'buyPrices' => $buyPrices,
            'planner' => new Gw2LegendaryPlanner($this->recipes, $buyPrices, $this->gw2->getStorableMaterialIds()),
            'achievementDefs' => $this->gw2->getAchievements($achievementIds),
            'accountAchievements' => $this->gw2->getAccountAchievements($achievementIds),
        ];
    }

    /** The in-game collections of a legendary, with the account's progress step by step */
    private function achievementsOf(int $legendaryId, array $context): array
    {
        $links = $this->recipes->achievements($legendaryId);
        $list = [];
        foreach (array_filter(array_merge([$links['collection']], $links['precursor'], $links['related'])) as $id) {
            $def = $context['achievementDefs'][$id] ?? null;
            if (!$def) {
                continue;
            }
            $account = $context['accountAchievements'][$id] ?? [];
            $doneBits = array_flip($account['bits'] ?? []);
            $done = $account['done'] ?? false;
            $list[] = [
                'id' => $id,
                'kind' => match (true) {
                    $id === $links['collection'] => 'collection',
                    in_array($id, $links['precursor'], true) => 'precursor',
                    default => 'related',
                },
                'name' => $def['name'],
                'requirement' => $this->plainText($def['requirement'] ?? ''),
                'done' => $done,
                'current' => $account['current'] ?? 0,
                'max' => $account['max'] ?? count($def['bits'] ?? []),
                'bits' => array_map(fn (int $i, array $bit) => [
                    'item' => 'Item' === ($bit['type'] ?? null) ? $bit['id'] : null,
                    'text' => isset($bit['text']) ? $this->plainText($bit['text']) : null,
                    'done' => $done || isset($doneBits[$i]),
                ], array_keys($def['bits'] ?? []), $def['bits'] ?? []),
            ];
        }

        return $list;
    }

    // GW2 texts embed their own markup (<c=@flavor>...</c>, <br>)
    private function plainText(string $text): string
    {
        return trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $text))));
    }

    /** Names and icons of every item and currency the response refers to, sent once */
    private function meta(array $context, array $itemIds): array
    {
        $itemIds = array_values(array_unique(array_filter($itemIds, fn ($id) => $id > 0)));
        $items = [];
        foreach ($this->gw2->getItems($itemIds) as $id => $item) {
            $items[$id] = [
                'name' => $item['name'] ?? null,
                'icon' => $item['icon'] ?? null,
                'rarity' => $item['rarity'] ?? null,
                'type' => $item['type'] ?? null,
                'subType' => $item['details']['type'] ?? null,
            ];
        }

        $currencyIds = $this->recipes->allCurrencyIds();
        $currencies = [];
        foreach ($currencyIds ? $this->gw2->getCurrencies(array_unique($currencyIds)) : [] as $id => $currency) {
            $currencies[$id] = ['name' => $currency['name'] ?? null, 'icon' => $currency['icon'] ?? null];
        }

        return ['items' => (object) $items, 'currencies' => (object) $currencies];
    }

    private function withLocations(array $rows, array $stock): array
    {
        return array_map(fn (array $row) => $row + [
            'locations' => 'item' === $row['type']
                ? ($stock['items'][$row['id']] ?? [])
                : ['wallet' => $stock['currencies'][$row['id']] ?? 0],
        ], $rows);
    }

    private function componentIds(array $summaries): array
    {
        $ids = [];
        foreach ($summaries as $summary) {
            foreach ($summary['components'] as $component) {
                if ('item' === $component['type']) {
                    $ids[] = $component['id'];
                }
            }
        }

        return $ids;
    }

    private function idsIn(array $node): array
    {
        $ids = 'item' === $node['type'] ? [$node['id']] : [];
        foreach ($node['children'] as $child) {
            $ids = array_merge($ids, $this->idsIn($child));
        }

        return $ids;
    }
}
