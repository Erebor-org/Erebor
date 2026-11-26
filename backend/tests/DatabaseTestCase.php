<?php

namespace App\Tests;

use App\Entity\Characters;
use App\Entity\Mule;
use App\Entity\Ranks;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base des tests qui exécutent du vrai SQL sur PostgreSQL.
 *
 * Préalable : `make test-db` (conteneur postgres-test + schéma). Chaque test tourne dans une
 * transaction annulée par dama/doctrine-test-bundle : la base reste vide entre deux tests.
 *
 * Garde-fou : refuse de tourner ailleurs que sur postgres-test / base *_test, pour ne jamais
 * écrire dans la base de dev distante que docker-compose exporte dans l'environnement.
 */
abstract class DatabaseTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $params = $this->em->getConnection()->getParams();
        $host = $params['host'] ?? '';
        $dbname = $params['dbname'] ?? '';
        if ($host !== 'postgres-test' || !str_ends_with($dbname, '_test')) {
            $this->fail(sprintf('Base de test inattendue (%s/%s) : refus de lancer un test SQL hors de postgres-test.', $host, $dbname));
        }

        try {
            $hasSchema = (bool) $this->em->getConnection()->fetchOne(
                "SELECT to_regclass('public.characters') IS NOT NULL"
            );
        } catch (\Throwable $e) {
            $this->markTestSkipped('postgres-test injoignable — lancez `make test-db`. (' . $e->getMessage() . ')');
        }
        if (!$hasSchema) {
            $this->markTestSkipped('Schéma de test absent — lancez `make test-db`.');
        }
    }

    protected function makeRank(string $name, ?int $requiredDays, bool $lead = false, bool $recruiter = false): Ranks
    {
        $rank = (new Ranks())
            ->setName($name)
            ->setRequiredDays($requiredDays)
            ->setDescription($name)
            ->setLead($lead)
            ->setRecruiter($recruiter);
        $this->em->persist($rank);

        return $rank;
    }

    protected function makeCharacter(string $pseudo, Ranks $rank, string $recruitedAt = '-30 days', ?Characters $recruiter = null): Characters
    {
        $character = (new Characters())
            ->setPseudo($pseudo)
            ->setAnkamaPseudo($pseudo . '#0000')
            ->setClass('Iop')
            ->setRecruitedAt(new \DateTime($recruitedAt))
            ->setRank($rank)
            ->setRecruiter($recruiter);
        $this->em->persist($character);

        return $character;
    }

    protected function makeMule(string $pseudo, Characters $main): Mule
    {
        $mule = (new Mule())
            ->setPseudo($pseudo)
            ->setAnkamaPseudo($pseudo . '#0000')
            ->setClass('Cra')
            ->setMainCharacter($main);
        $main->addMule($mule);
        $this->em->persist($mule);

        return $mule;
    }

    protected function makeUser(string $username, array $roles, ?Characters $character = null): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setRoles($roles)
            ->setPassword('test')
            ->setCharacterId($character?->getId());
        $this->em->persist($user);

        return $user;
    }

    /** Requête HTTP authentifiée par un vrai JWT (le firewall est stateless). */
    protected function requestAs(User $user, string $method, string $uri, ?array $json = null): array
    {
        $token = static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
        $this->client->request($method, $uri, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], $json === null ? null : json_encode($json));

        $content = $this->client->getResponse()->getContent();

        return json_decode($content ?: 'null', true) ?? [];
    }

    /** @return list<array<string, mixed>> */
    protected function events(?string $type = null): array
    {
        $sql = 'SELECT * FROM guild_event' . ($type ? ' WHERE type = :type' : '') . ' ORDER BY id';
        $rows = $this->em->getConnection()->fetchAllAssociative($sql, $type ? ['type' => $type] : []);

        return array_map(function (array $row) {
            $row['payload'] = json_decode($row['payload'], true);
            return $row;
        }, $rows);
    }
}
