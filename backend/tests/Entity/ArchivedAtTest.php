<?php

namespace App\Tests\Entity;

use App\Entity\Characters;
use App\Entity\Mule;
use PHPUnit\Framework\TestCase;

/**
 * La date de départ est tenue par setIsArchived() : c'est elle qui alimente toutes les stats
 * de sortie et de rétention, d'où ces règles explicites.
 */
class ArchivedAtTest extends TestCase
{
    /** @return iterable<string, array{object}> */
    public static function archivables(): iterable
    {
        yield 'personnage' => [new Characters()];
        yield 'mule' => [new Mule()];
    }

    /** @dataProvider archivables */
    public function testActiveEntityHasNoDepartureDate(Characters|Mule $entity): void
    {
        $this->assertFalse($entity->isArchived());
        $this->assertNull($entity->getArchivedAt());
    }

    /** @dataProvider archivables */
    public function testArchivingStampsTheDepartureDate(Characters|Mule $entity): void
    {
        $before = new \DateTime('-1 second');
        $entity->setIsArchived(true);

        $this->assertTrue($entity->isArchived());
        $this->assertNotNull($entity->getArchivedAt());
        $this->assertGreaterThanOrEqual($before, $entity->getArchivedAt());
    }

    /** @dataProvider archivables */
    public function testArchivingTwiceKeepsTheFirstDepartureDate(Characters|Mule $entity): void
    {
        $entity->setIsArchived(true);
        $entity->setArchivedAt($first = new \DateTime('2026-01-15 10:00:00'));

        $entity->setIsArchived(true);

        $this->assertSame($first, $entity->getArchivedAt());
    }

    /** @dataProvider archivables */
    public function testUnarchivingClearsTheDepartureDate(Characters|Mule $entity): void
    {
        $entity->setIsArchived(true);
        $entity->setIsArchived(false);

        $this->assertFalse($entity->isArchived());
        $this->assertNull($entity->getArchivedAt());
    }

    public function testNewMuleIsTimestamped(): void
    {
        $this->assertNotNull((new Mule())->getCreatedAt());
    }
}
