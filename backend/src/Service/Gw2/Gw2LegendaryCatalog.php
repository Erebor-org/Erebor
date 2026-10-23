<?php

namespace App\Service\Gw2;

/**
 * Legendaries that can be tracked. Their recipe trees are in data/gw2/legendary_recipes.json,
 * built by app:gw2:import-legendary-recipes: re-run it after adding an item here.
 */
final class Gw2LegendaryCatalog
{
    public const ITEMS = [
        // Generation 1 (core game)
        ['id' => 30684, 'generation' => 1],
        ['id' => 30685, 'generation' => 1],
        ['id' => 30686, 'generation' => 1],
        ['id' => 30687, 'generation' => 1],
        ['id' => 30688, 'generation' => 1],
        ['id' => 30689, 'generation' => 1],
        ['id' => 30690, 'generation' => 1],
        ['id' => 30691, 'generation' => 1],
        ['id' => 30692, 'generation' => 1],
        ['id' => 30693, 'generation' => 1],
        ['id' => 30694, 'generation' => 1],
        ['id' => 30695, 'generation' => 1],
        ['id' => 30696, 'generation' => 1],
        ['id' => 30697, 'generation' => 1],
        ['id' => 30698, 'generation' => 1],
        ['id' => 30699, 'generation' => 1],
        ['id' => 30700, 'generation' => 1],
        ['id' => 30701, 'generation' => 1],
        ['id' => 30702, 'generation' => 1],
        ['id' => 30703, 'generation' => 1],
        ['id' => 30704, 'generation' => 1],
        // Generation 3 (End of Dragons, Aurene's weapons)
        ['id' => 95612, 'generation' => 3],
        ['id' => 95675, 'generation' => 3],
        ['id' => 95684, 'generation' => 3],
        ['id' => 95808, 'generation' => 3],
        ['id' => 96028, 'generation' => 3],
        ['id' => 96203, 'generation' => 3],
        ['id' => 96221, 'generation' => 3],
        ['id' => 96356, 'generation' => 3],
        ['id' => 96652, 'generation' => 3],
        ['id' => 96937, 'generation' => 3],
        ['id' => 97077, 'generation' => 3],
        ['id' => 97099, 'generation' => 3],
        ['id' => 97165, 'generation' => 3],
        ['id' => 97377, 'generation' => 3],
        ['id' => 97590, 'generation' => 3],
        ['id' => 97783, 'generation' => 3],
    ];

    public static function has(int $id): bool
    {
        return in_array($id, array_column(self::ITEMS, 'id'), true);
    }

    public static function generation(int $id): ?int
    {
        foreach (self::ITEMS as $item) {
            if ($item['id'] === $id) {
                return $item['generation'];
            }
        }

        return null;
    }
}
