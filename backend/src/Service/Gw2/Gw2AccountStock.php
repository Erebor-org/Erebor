<?php

namespace App\Service\Gw2;

/**
 * Everything the account owns that can go into a recipe: items in the bank, the material
 * storage, every character's bags and the shared slots, plus wallet currencies.
 */
class Gw2AccountStock
{
    public const BANK = 'bank';
    public const MATERIALS = 'materials';
    public const INVENTORY = 'inventory';
    public const SHARED = 'shared';

    public function __construct(private Gw2ApiClient $gw2)
    {
    }

    /**
     * @param bool $refresh skip the 5 minute cache
     *
     * @return array{items: array<int, array<string, int>>, currencies: array<int, int>, unlocked: int[]}
     *               items: item id => [location => count]
     */
    public function load(bool $refresh = false): array
    {
        $names = $this->gw2->getCharacterNames();
        if ($refresh) {
            $this->gw2->forgetInventories($names);
            $names = $this->gw2->getCharacterNames();
        }

        $items = [];
        $add = function (?array $slot, string $location) use (&$items) {
            if (null !== $slot && ($slot['count'] ?? 0) > 0) {
                $items[$slot['id']][$location] = ($items[$slot['id']][$location] ?? 0) + $slot['count'];
            }
        };

        foreach ($this->gw2->getBank() as $slot) {
            $add($slot, self::BANK);
        }
        foreach ($this->gw2->getMaterialStorage() as $slot) {
            $add($slot, self::MATERIALS);
        }
        foreach ($this->gw2->getSharedInventory() as $slot) {
            $add($slot, self::SHARED);
        }
        foreach ($this->gw2->getCharacterInventories($names) as $inventory) {
            foreach ($inventory['bags'] ?? [] as $bag) {
                foreach ($bag['inventory'] ?? [] as $slot) {
                    $add($slot, self::INVENTORY);
                }
            }
        }

        return [
            'items' => $items,
            'currencies' => array_column($this->gw2->getWallet(), 'value', 'id'),
            'unlocked' => array_column(array_filter($this->gw2->getLegendaryArmory(), fn ($l) => ($l['count'] ?? 0) > 0), 'id'),
        ];
    }

    /** Total owned per item id, all locations together */
    public static function totals(array $items): array
    {
        return array_map('array_sum', $items);
    }
}
