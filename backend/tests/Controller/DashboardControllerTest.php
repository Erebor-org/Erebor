<?php

namespace App\Tests\Controller;

use App\Tests\DatabaseTestCase;

class DashboardControllerTest extends DatabaseTestCase
{
    public function testMembersGetTheDashboardWithoutAdminBlocks(): void
    {
        $member = $this->makeCharacter('Membre', $this->makeRank('Néophyte', 1), '-3 days');
        $this->em->flush();
        $user = $this->makeUser('membre', ['ROLE_USER'], $member);
        $this->em->flush();

        $body = $this->requestAs($user, 'GET', '/dashboard/home');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $body['counters']['active']['value']);
        $this->assertCount(12, $body['monthly']);
        $this->assertSame('Membre', $body['myProgress']['pseudo']);
        $this->assertSame(['position' => 1, 'total' => 1, 'topPercent' => 100, 'placesGained' => null], $body['mySeniority']);
        $this->assertArrayHasKey('rate', $body['retention61']);
        $this->assertSame('joined', $body['movements'][0]['type']);
        $this->assertSame([], $body['recentPromotions']);
        $this->assertNull($body['admin'], 'les blocs de gestion ne sortent pas du serveur pour un simple membre');
    }

    public function testAdminsAlsoGetManagementBlocks(): void
    {
        $this->makeCharacter('Recrue', $this->makeRank('Néophyte', 1), '-3 days');
        $admin = $this->makeUser('enroleur', ['ROLE_ADMIN']);
        $this->em->flush();

        $body = $this->requestAs($admin, 'GET', '/dashboard/home');

        $this->assertResponseIsSuccessful();
        $this->assertNull($body['admin']['ghost']);
        $this->assertSame('Recrue', $body['admin']['freshRecruits'][0]['pseudo']);
    }

    public function testActivityPagesAreBounded(): void
    {
        $rank = $this->makeRank('Néophyte', 1);
        for ($i = 1; $i <= 3; $i++) {
            $this->makeCharacter("M$i", $rank, "-$i days");
        }
        $user = $this->makeUser('membre', ['ROLE_USER']);
        $this->em->flush();

        $body = $this->requestAs($user, 'GET', '/dashboard/activity?limit=2&offset=0');
        $this->assertSame(['M1', 'M2'], array_column($body['items'], 'pseudo'));
        $this->assertTrue($body['hasMore']);

        $body = $this->requestAs($user, 'GET', '/dashboard/activity?limit=2&offset=2');
        $this->assertSame(['M3'], array_column($body['items'], 'pseudo'));
        $this->assertFalse($body['hasMore']);

        $body = $this->requestAs($user, 'GET', '/dashboard/activity?kind=promotions');
        $this->assertSame([], $body['items']);

        $this->requestAs($user, 'GET', '/dashboard/activity?kind=nimportequoi');
        $this->assertResponseStatusCodeSame(400);
    }

    public function testAnonymousIsRejected(): void
    {
        $this->client->request('GET', '/dashboard/home');
        $this->assertResponseStatusCodeSame(401);
    }
}
