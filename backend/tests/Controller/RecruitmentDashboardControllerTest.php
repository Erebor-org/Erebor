<?php

namespace App\Tests\Controller;

use App\Entity\Characters;
use App\Entity\User;
use App\Service\AppSettings;
use App\Tests\DatabaseTestCase;

/**
 * Droits du dashboard recrutement, contrôlés côté serveur.
 */
class RecruitmentDashboardControllerTest extends DatabaseTestCase
{
    private Characters $alice;
    private Characters $bob;

    protected function setUp(): void
    {
        parent::setUp();
        $rank = $this->makeRank('Néophyte', 1);
        $enroleur = $this->makeRank('Enroleur', null, false, true);
        $this->alice = $this->makeCharacter('Alice', $enroleur, '2024-01-01');
        $this->bob = $this->makeCharacter('Bob', $enroleur, '2024-01-01');
        $this->makeCharacter('RecrueAlice', $rank, '-100 days', $this->alice);
        $this->makeCharacter('RecrueBob1', $rank, '-100 days', $this->bob);
        $this->makeCharacter('RecrueBob2', $rank, '-100 days', $this->bob);
        $this->makeCharacter('RecrueBob3', $rank, '-2 days', $this->bob);
        $this->em->flush();
    }

    private function setScope(string $scope): void
    {
        static::getContainer()->get(AppSettings::class)->set(AppSettings::RECRUITMENT_ADMIN_SCOPE, $scope);
    }

    private function enroleur(?Characters $character): User
    {
        $user = $this->makeUser('enroleur', ['ROLE_ADMIN'], $character);
        $this->em->flush();

        return $user;
    }

    public function testSuperAdminsSeeEverything(): void
    {
        $this->setScope(AppSettings::SCOPE_NONE);
        $conseiller = $this->makeUser('conseiller', ['ROLE_SUPER_ADMIN']);
        $this->em->flush();

        $body = $this->requestAs($conseiller, 'GET', '/recruitment/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSame('all', $body['access']['scope']);
        $this->assertSame(['Bob', 'Alice'], array_column($body['leaderboard']['entries'], 'pseudo'));
        $this->assertSame(4, array_sum(array_column($body['cohorts'], 'recruits')));
        $this->assertFalse($body['access']['canConfigure']);
    }

    public function testEnroleurWithOwnScopeSeesOnlyOwnRecruitsAndPosition(): void
    {
        $this->setScope(AppSettings::SCOPE_OWN);
        $body = $this->requestAs($this->enroleur($this->alice), 'GET', '/recruitment/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSame('own', $body['access']['scope']);
        $this->assertSame('Alice', $body['access']['recruiter']['pseudo']);
        $this->assertSame(['Alice'], array_column($body['recruiters'], 'pseudo'));
        $this->assertNull($body['leaderboard']['entries'], 'pas le classement des autres');
        $this->assertSame(2, $body['leaderboard']['mine']['position']);
        $this->assertSame(2, $body['leaderboard']['mine']['totalRecruiters']);
        $this->assertSame(1, array_sum(array_column($body['cohorts'], 'recruits')));
        $this->assertStringNotContainsString('RecrueBob', json_encode($body), 'aucune donnée sur les recrues de Bob');
    }

    public function testEnroleurWithoutCharacterSeesNothing(): void
    {
        $this->setScope(AppSettings::SCOPE_OWN);
        $body = $this->requestAs($this->enroleur(null), 'GET', '/recruitment/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $body['recruiters']);
        $this->assertSame(0, array_sum(array_column($body['cohorts'], 'recruits')));
        $this->assertNull($body['leaderboard']['mine']);
    }

    public function testEnroleurWithAllScopeSeesEverything(): void
    {
        $this->setScope(AppSettings::SCOPE_ALL);
        $body = $this->requestAs($this->enroleur($this->alice), 'GET', '/recruitment/dashboard');

        $this->assertSame('all', $body['access']['scope']);
        $this->assertCount(2, $body['leaderboard']['entries']);
    }

    public function testEnroleurWithNoneScopeAndMembersAreRejected(): void
    {
        $this->setScope(AppSettings::SCOPE_NONE);
        $this->requestAs($this->enroleur($this->alice), 'GET', '/recruitment/dashboard');
        $this->assertResponseStatusCodeSame(403);

        $membre = $this->makeUser('membre', ['ROLE_USER']);
        $this->em->flush();
        $this->requestAs($membre, 'GET', '/recruitment/dashboard');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testOwnersCanConfigureAndParametersAreValidated(): void
    {
        $meneur = $this->makeUser('meneur', ['ROLE_OWNERS']);
        $this->em->flush();

        $body = $this->requestAs($meneur, 'GET', '/recruitment/dashboard?week=2026-W10&months=6');
        $this->assertTrue($body['access']['canConfigure']);
        $this->assertSame(['week' => '2026-W10', 'months' => 6], $body['filters']);
        $this->assertSame('2026-03-02', $body['week']['start']);

        $this->requestAs($meneur, 'GET', '/recruitment/dashboard?week=2026-W99');
        $this->assertResponseStatusCodeSame(400);
        $this->requestAs($meneur, 'GET', '/recruitment/dashboard?months=5');
        $this->assertResponseStatusCodeSame(400);
    }
}
