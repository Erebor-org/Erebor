<?php

namespace App\Service;

use App\Entity\DofusdleMonster;

class ClassicComparator
{
    public function compare(DofusdleMonster $guess, DofusdleMonster $solution): array
    {
        // Race
        $race = ($guess->getRace() === $solution->getRace()) ? 'match' : 'miss';

        // Level range comparison
        $level = $this->compareLevelRange($guess, $solution);

        // AP range comparison
        $ap = $this->compareStatRange($guess->getApMin(), $guess->getApMax(), $solution->getApMin(), $solution->getApMax());

        // MP range comparison
        $mp = $this->compareStatRange($guess->getMpMin(), $guess->getMpMax(), $solution->getMpMin(), $solution->getMpMax());

        // HP range comparison
        $hp = $this->compareStatRange($guess->getHpMin(), $guess->getHpMax(), $solution->getHpMin(), $solution->getHpMax());

        // Zone comparison (area + subarea)
        $zone = $this->compareZone($guess, $solution);

        // Résistance dominante
        $resistDominant = $this->compareDominantResistance($guess, $solution);

        return [
            'race' => $race,
            'level' => $level,
            'ap' => $ap,
            'mp' => $mp,
            'hp' => $hp,
            'zone' => $zone,
            'resistDominant' => $resistDominant,
        ];
    }

    /**
     * Compare les ranges de level (ex: 1-15 vs 10-20 = partial)
     */
    private function compareLevelRange(DofusdleMonster $guess, DofusdleMonster $solution): string
    {
        $gMin = $guess->getLevelMin();
        $gMax = $guess->getLevelMax();
        $sMin = $solution->getLevelMin();
        $sMax = $solution->getLevelMax();

        if (!$gMin || !$gMax || !$sMin || !$sMax) {
            return 'unknown';
        }

        // Si les ranges se chevauchent
        if (($gMin <= $sMax && $gMax >= $sMin)) {
            // Si c'est exactement le même range
            if ($gMin === $sMin && $gMax === $sMax) {
                return 'exact';
            }
            // Si c'est un chevauchement partiel
            return 'partial';
        }

        // Pas de chevauchement
        if ($gMax < $sMin) return 'up';
        if ($gMin > $sMax) return 'down';
        
        return 'miss';
    }

    /**
     * Compare les ranges de stats (PA, PM, HP)
     */
    private function compareStatRange(?int $gMin, ?int $gMax, ?int $sMin, ?int $sMax): string
    {
        if (!$gMin || !$gMax || !$sMin || !$sMax) {
            return 'unknown';
        }

        // Si les ranges se chevauchent
        if (($gMin <= $sMax && $gMax >= $sMin)) {
            // Si c'est exactement le même range
            if ($gMin === $sMin && $gMax === $sMax) {
                return 'exact';
            }
            // Si c'est un chevauchement partiel
            return 'partial';
        }

        // Pas de chevauchement
        if ($gMax < $sMin) return 'up';
        if ($gMin > $sMax) return 'down';
        
        return 'miss';
    }

    /**
     * Compare les zones (area + subarea)
     */
    private function compareZone(DofusdleMonster $guess, DofusdleMonster $solution): string
    {
        $gArea = $guess->getArea();
        $gSubarea = $guess->getSubarea();
        $sArea = $solution->getArea();
        $sSubarea = $solution->getSubarea();

        if (!$gArea || !$gSubarea || !$sArea || !$sSubarea) {
            return 'unknown';
        }

        // Si c'est exactement la même zone
        if ($gArea === $sArea && $gSubarea === $sSubarea) {
            return 'exact';
        }

        // Si c'est la même area mais subarea différente
        if ($gArea === $sArea) {
            return 'partial';
        }

        return 'miss';
    }

    /**
     * Compare les résistances dominantes
     */
    private function compareDominantResistance(DofusdleMonster $guess, DofusdleMonster $solution): string
    {
        $gResistances = $guess->getResistancesMax();
        $sResistances = $solution->getResistancesMax();

        if (!$gResistances || !$sResistances) {
            return 'unknown';
        }

        // Trouver la résistance dominante (la plus élevée)
        $gDominant = $this->getDominantResistance($gResistances);
        $sDominant = $this->getDominantResistance($sResistances);

        if (!$gDominant || !$sDominant) {
            return 'unknown';
        }

        // Si c'est le même élément
        if ($gDominant['element'] === $sDominant['element']) {
            // Comparer les valeurs
            if ($gDominant['value'] === $sDominant['value']) {
                return 'exact';
            }
            if ($gDominant['value'] < $sDominant['value']) {
                return 'up';
            }
            return 'down';
        }

        return 'miss';
    }

    /**
     * Trouve la résistance dominante d'un monstre
     */
    private function getDominantResistance(array $resistances): ?array
    {
        $maxValue = -999;
        $dominantElement = null;

        foreach ($resistances as $element => $value) {
            if ($value > $maxValue) {
                $maxValue = $value;
                $dominantElement = $element;
            }
        }

        if ($dominantElement === null) {
            return null;
        }

        return [
            'element' => $dominantElement,
            'value' => $maxValue
        ];
    }
}

