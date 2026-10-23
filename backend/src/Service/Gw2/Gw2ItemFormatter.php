<?php

namespace App\Service\Gw2;

/**
 * Turns item slots (worn equipment, bag, shared inventory or bank slots: they share the same
 * shape) into what the in-game tooltip shows: stat combination and attributes, defense or
 * weapon strength, runes/sigils and infusions. Item and stat names come in French.
 */
class Gw2ItemFormatter
{
    public function __construct(private Gw2ApiClient $gw2)
    {
    }

    /**
     * @param array $slots non-empty slots ({id, count?, upgrades?, infusions?, stats?, binding?...})
     *
     * @return array formatted slots, same order and keys as $slots
     */
    public function formatSlots(array $slots): array
    {
        if (!$slots) {
            return [];
        }

        // One batched /items call for the items and everything slotted in them
        $itemIds = [];
        foreach ($slots as $slot) {
            $itemIds = array_merge($itemIds, [$slot['id']], $slot['upgrades'] ?? [], $slot['infusions'] ?? []);
        }
        $items = $this->gw2->getItems($itemIds);

        // Selectable stats (ascended, legendary...) live on the slot, fixed ones on the item
        $statIdOf = fn (array $slot) => $slot['stats']['id'] ?? $items[$slot['id']]['details']['infix_upgrade']['id'] ?? null;
        $statIds = array_filter(array_map($statIdOf, $slots));
        $stats = $statIds ? $this->gw2->getItemStats($statIds) : [];

        return array_map(function (array $slot) use ($items, $stats, $statIdOf) {
            $item = $items[$slot['id']] ?? [];
            $details = $item['details'] ?? [];

            if (isset($slot['stats']['attributes'])) {
                $attributes = array_map(
                    fn (string $attribute, int|float $modifier) => ['attribute' => $attribute, 'modifier' => $modifier],
                    array_keys($slot['stats']['attributes']),
                    $slot['stats']['attributes'],
                );
            } else {
                $attributes = $details['infix_upgrade']['attributes'] ?? [];
            }

            return [
                'slot' => $slot['slot'] ?? null,
                'id' => $slot['id'],
                'count' => $slot['count'] ?? 1,
                'name' => $item['name'] ?? null,
                'rarity' => $item['rarity'] ?? null,
                'level' => $item['level'] ?? null,
                'icon' => $item['icon'] ?? null,
                'itemType' => $item['type'] ?? null,
                'subType' => $details['type'] ?? null,
                'weightClass' => $details['weight_class'] ?? null,
                'defense' => ($details['defense'] ?? 0) ?: null,
                'power' => isset($details['min_power']) ? [$details['min_power'], $details['max_power']] : null,
                'statsName' => $stats[$statIdOf($slot)]['name'] ?? null,
                'attributes' => array_values(array_map(fn (array $a) => [
                    'attribute' => $a['attribute'],
                    'modifier' => $a['modifier'],
                ], $attributes)),
                'upgrades' => array_values(array_map(fn (int $id) => $this->formatUpgrade($id, $items), $slot['upgrades'] ?? [])),
                'infusions' => array_values(array_map(fn (int $id) => $this->formatUpgrade($id, $items), $slot['infusions'] ?? [])),
                'binding' => $slot['binding'] ?? null,
                'flags' => $item['flags'] ?? [],
                'vendorValue' => $item['vendor_value'] ?? 0,
                'description' => $this->plainText($item['description'] ?? ''),
            ];
        }, $slots);
    }

    private function formatUpgrade(int $id, array $items): array
    {
        $details = $items[$id]['details'] ?? [];
        // Runes list their set bonuses, sigils and infusions describe a buff and/or flat attributes
        $bonuses = $details['bonuses'] ?? [];
        if (!$bonuses && !empty($details['infix_upgrade']['buff']['description'])) {
            $bonuses = [$details['infix_upgrade']['buff']['description']];
        }

        return [
            'id' => $id,
            'name' => $items[$id]['name'] ?? null,
            'icon' => $items[$id]['icon'] ?? null,
            'bonuses' => array_values(array_filter(array_map(fn (string $b) => $this->plainText($b), $bonuses))),
            'attributes' => array_values(array_map(fn (array $a) => [
                'attribute' => $a['attribute'],
                'modifier' => $a['modifier'],
            ], $details['infix_upgrade']['attributes'] ?? [])),
        ];
    }

    // GW2 texts embed their own markup (<c=@flavor>...</c>, <br>)
    private function plainText(string $text): string
    {
        return trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $text))));
    }
}
