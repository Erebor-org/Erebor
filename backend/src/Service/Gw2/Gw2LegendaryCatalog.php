<?php

namespace App\Service\Gw2;

/**
 * Legendaries that can be tracked: weapons (generation 1 and 3) and trinkets. Their recipe
 * trees are in data/gw2/legendary_recipes.json,
 * built by app:gw2:import-legendary-recipes: re-run it after adding an item here.
 */
final class Gw2LegendaryCatalog
{
    public const ITEMS = [
        // Generation 1 (core game)
        ['id' => 30684, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30685, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30686, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30687, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30688, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30689, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30690, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30691, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30692, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30693, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30694, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30695, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30696, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30697, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30698, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30699, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30700, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30701, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30702, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30703, 'generation' => 1, 'category' => 'weapon'],
        ['id' => 30704, 'generation' => 1, 'category' => 'weapon'],
        // Generation 3 (End of Dragons, Aurene's weapons)
        ['id' => 95612, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 95675, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 95684, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 95808, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 96028, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 96203, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 96221, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 96356, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 96652, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 96937, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 97077, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 97099, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 97165, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 97377, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 97590, 'generation' => 3, 'category' => 'weapon'],
        ['id' => 97783, 'generation' => 3, 'category' => 'weapon'],
        // Trinkets (accessories, rings, amulets)
        ['id' => 81908, 'generation' => null, 'category' => 'trinket'], // Aurora
        ['id' => 91048, 'generation' => null, 'category' => 'trinket'], // Vision
        ['id' => 91234, 'generation' => null, 'category' => 'trinket'], // Coalescence
        ['id' => 92991, 'generation' => null, 'category' => 'trinket'], // Transcendance
        ['id' => 93105, 'generation' => null, 'category' => 'trinket'], // Confluence
        ['id' => 95380, 'generation' => null, 'category' => 'trinket'], // Tenue du champion prismatique
        ['id' => 107022, 'generation' => null, 'category' => 'trinket'], // Été infini
        ['id' => 109012, 'generation' => null, 'category' => 'trinket'], // Discorde infinie
        ['id' => 109070, 'generation' => null, 'category' => 'trinket'], // Stella Radians
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

    /** 'weapon' or 'trinket' */
    public static function category(int $id): ?string
    {
        foreach (self::ITEMS as $item) {
            if ($item['id'] === $id) {
                return $item['category'];
            }
        }

        return null;
    }
}
