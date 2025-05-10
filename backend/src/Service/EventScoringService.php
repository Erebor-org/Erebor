<?php

namespace App\Service;

class EventScoringService
{
    /**
     * Formula 1-like scoring system
     * Position => Points
     */
    private const SCORING_SYSTEM = [
        1 => 25,  // 1st place: 25 points
        2 => 18,  // 2nd place: 18 points
        3 => 15,  // 3rd place: 15 points
        4 => 12,  // 4th place: 12 points
        5 => 10,  // 5th place: 10 points
        6 => 8,   // 6th place: 8 points
        7 => 6,   // 7th place: 6 points
        8 => 4,   // 8th place: 4 points
        9 => 2,   // 9th place: 2 points
        10 => 1,  // 10th place: 1 point
    ];

    /**
     * Get points for a given position
     */
    public function getPointsForPosition(int $position): int
    {
        return self::SCORING_SYSTEM[$position] ?? 0;
    }

    /**
     * Get all positions with their corresponding points
     */
    public function getScoringSystem(): array
    {
        return self::SCORING_SYSTEM;
    }

    /**
     * Check if a position is eligible for points
     */
    public function isPositionEligibleForPoints(int $position): bool
    {
        return isset(self::SCORING_SYSTEM[$position]);
    }

    /**
     * Get the maximum position that can earn points
     */
    public function getMaxScoringPosition(): int
    {
        return max(array_keys(self::SCORING_SYSTEM));
    }

    /**
     * Get the minimum position that can earn points
     */
    public function getMinScoringPosition(): int
    {
        return min(array_keys(self::SCORING_SYSTEM));
    }
}
