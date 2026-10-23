<?php

namespace App\Tests\Controller;

use App\Controller\Gw2Controller;
use App\Entity\User;
use App\Security\EffectiveRoles;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The GW2 section must only open to ROLE_DAMS, and no other role may inherit ROLE_DAMS
 * (an OWNER doesn't get it), while ROLE_DAMS inherits every other role. Uses the real security config, without
 * touching the database: decisions are made on in-memory users.
 */
class Gw2AccessControlTest extends WebTestCase
{
    private const GW2_PATHS = ['/gw2/tokeninfo', '/gw2/characters', '/gw2/characters/Siisko%20Siam', '/gw2/inventory'];

    public function testAnonymousRequestsAreRejected(): void
    {
        $client = static::createClient();

        foreach (self::GW2_PATHS as $path) {
            $client->request('GET', $path);
            $this->assertSame(401, $client->getResponse()->getStatusCode(), $path);
        }
    }

    public function testAccessControlRequiresRoleDamsOnTheWholePrefix(): void
    {
        $accessMap = static::getContainer()->get('security.access_map');

        foreach (self::GW2_PATHS as $path) {
            [$attributes] = $accessMap->getPatterns(Request::create($path));
            $this->assertSame(['ROLE_DAMS'], $attributes, $path);
        }
    }

    public function testControllerIsGuardedByIsGranted(): void
    {
        $attributes = (new \ReflectionClass(Gw2Controller::class))->getAttributes(IsGranted::class);

        $this->assertCount(1, $attributes);
        $this->assertSame('ROLE_DAMS', $attributes[0]->newInstance()->attribute);
    }

    public function roleProvider(): iterable
    {
        yield 'user' => [['ROLE_USER'], false];
        yield 'admin' => [['ROLE_ADMIN'], false];
        yield 'super admin' => [['ROLE_SUPER_ADMIN'], false];
        yield 'owner' => [['ROLE_OWNERS'], false];
        yield 'dams' => [['ROLE_DAMS'], true];
        yield 'owner + dams' => [['ROLE_OWNERS', 'ROLE_DAMS'], true];
    }

    /**
     * @dataProvider roleProvider
     */
    public function testOnlyRoleDamsIsGranted(array $roles, bool $expected): void
    {
        $user = (new User())->setUsername('tester')->setRoles($roles);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        $decisionManager = static::getContainer()->get('security.access.decision_manager');

        $this->assertSame($expected, $decisionManager->decide($token, ['ROLE_DAMS']));
    }

    public function testRoleDamsInheritsEveryOtherRole(): void
    {
        $user = (new User())->setUsername('dams')->setRoles(['ROLE_DAMS']);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $decisionManager = static::getContainer()->get('security.access.decision_manager');

        foreach (['ROLE_OWNERS', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'] as $role) {
            $this->assertTrue($decisionManager->decide($token, [$role]), $role);
        }

        // The roles sent to the frontend include the inherited ones, so its includes() checks pass
        $roles = static::getContainer()->get(EffectiveRoles::class)->of($user);
        foreach (['ROLE_DAMS', 'ROLE_OWNERS', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'] as $role) {
            $this->assertContains($role, $roles);
        }

        $owner = (new User())->setUsername('owner')->setRoles(['ROLE_OWNERS']);
        $this->assertNotContains('ROLE_DAMS', static::getContainer()->get(EffectiveRoles::class)->of($owner));
    }
}
