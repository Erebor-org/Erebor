<?php

namespace App\Tests\Controller;

use App\Service\AppSettings;
use App\Tests\DatabaseTestCase;

class AppSettingsControllerTest extends DatabaseTestCase
{
    public function testMissingSettingFallsBackToDefault(): void
    {
        $this->em->getConnection()->executeStatement('DELETE FROM app_setting');

        $this->assertSame(AppSettings::SCOPE_OWN, static::getContainer()->get(AppSettings::class)->get(AppSettings::RECRUITMENT_ADMIN_SCOPE));
    }

    public function testOwnersCanChangeTheRecruitmentScope(): void
    {
        $owner = $this->makeUser('meneur', ['ROLE_OWNERS']);
        $this->em->flush();

        $body = $this->requestAs($owner, 'PUT', '/admin/settings', [AppSettings::RECRUITMENT_ADMIN_SCOPE => 'all']);

        $this->assertResponseIsSuccessful();
        $this->assertSame('all', $body['settings'][AppSettings::RECRUITMENT_ADMIN_SCOPE]);
        $this->assertSame('all', static::getContainer()->get(AppSettings::class)->get(AppSettings::RECRUITMENT_ADMIN_SCOPE));
    }

    public function testInvalidValuesAndUnknownKeysAreRejected(): void
    {
        $owner = $this->makeUser('meneur', ['ROLE_OWNERS']);
        $this->em->flush();

        $this->requestAs($owner, 'PUT', '/admin/settings', [AppSettings::RECRUITMENT_ADMIN_SCOPE => 'everything']);
        $this->assertResponseStatusCodeSame(400);

        $this->requestAs($owner, 'PUT', '/admin/settings', ['inconnu' => 'x']);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testBelowOwnersIsForbidden(): void
    {
        $conseiller = $this->makeUser('conseiller', ['ROLE_SUPER_ADMIN']);
        $this->em->flush();

        $this->requestAs($conseiller, 'GET', '/admin/settings');
        $this->assertResponseStatusCodeSame(403);

        $this->requestAs($conseiller, 'PUT', '/admin/settings', [AppSettings::RECRUITMENT_ADMIN_SCOPE => 'all']);
        $this->assertResponseStatusCodeSame(403);
    }
}
