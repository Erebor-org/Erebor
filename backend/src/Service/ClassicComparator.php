<?php

namespace App\Service;

use App\Entity\DofusdleMonster;

class ClassicComparator
{
    public function compare(DofusdleMonster $guess, DofusdleMonster $solution): array
    {
        // Family (race)
        $family = ($guess->getFamily() === $solution->getFamily()) ? 'match' : 'miss';

        // Rank (boss/miniboss/normal)
        $rank = ($this->getRank($guess) === $this->getRank($solution)) ? 'match' : 'miss';

        // Level
        $gLevel = $guess->getLevel();
        $sLevel = $solution->getLevel();
        $level = $gLevel === $sLevel ? 'eq' : ($gLevel > $sLevel ? 'up' : 'down');

        // Element (dominant tag parmi water/fire/earth/air/neutral)
        $gElem = $this->getDominantElement($guess);
        $sElem = $this->getDominantElement($solution);
        $element = ($gElem && $gElem === $sElem) ? 'match' : 'miss';

        // AP/MP/HP
        $ap = $this->compareStat($guess->getPa(), $solution->getPa());
        $mp = $this->compareStat($guess->getPm(), $solution->getPm());
        $hp = $this->compareStat($guess->getPdv(), $solution->getPdv());

        // Zone (subareas/areas)
        $zone = $this->compareZone($guess, $solution);

        // Résistance dominante
        $gRes = $this->getDominantResist($guess);
        $sRes = $this->getDominantResist($solution);
        $resistDominant = ($gRes && $gRes === $sRes) ? 'match' : 'miss';

        return [
            'family' => $family,
            'zone' => $zone,
            'level' => $level,
            'rank' => $rank,
            'element' => $element,
            'ap' => $ap,
            'mp' => $mp,
            'hp' => $hp,
            'resistDominant' => $resistDominant,
        ];
    }

    private function getRank(DofusdleMonster $m): string
    {
        if ($m->isBoss()) return 'boss';
        // Ajoute ici la logique pour mini-boss si tu as un champ dédié
        if (method_exists($m, 'isMiniBoss') && $m->isMiniBoss()) return 'mini-boss';
        if (method_exists($m, 'getBossType') && $m->getBossType() === 'mini-boss') return 'mini-boss';
        return 'normal';
    }

    private function getDominantElement(DofusdleMonster $m): ?string
    {
        $tags = $m->getTags() ?? [];
        foreach (['water','fire','earth','air','neutral'] as $el) {
            if (in_array($el, $tags, true)) return $el;
        }
        return null;
    }

    private function compareStat($g, $s): string
    {
        if ($g === $s) return 'eq';
        if ($g > $s) return 'up';
        return 'down';
    }

    private function compareZone(DofusdleMonster $g, DofusdleMonster $s): string
    {
        $gSub = $g->getSubareas() ?? [];
        $sSub = $s->getSubareas() ?? [];
        $gArea = $g->getAreas() ?? [];
        $sArea = $s->getAreas() ?? [];
        if (array_intersect($gSub, $sSub)) return 'exact';
        if (array_intersect($gArea, $sArea)) return 'overlap';
        return 'miss';
    }

    private function getDominantResist(DofusdleMonster $m): ?string
    {
        // Si tu as un champ ref/resistances, adapte ici
        // Ex: $m->getRefRes() ou $m->getRef()['res']
        return null;
    }
}

