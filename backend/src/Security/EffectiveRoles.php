<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Roles sent to the frontend for the logged-in user, expanded through security.role_hierarchy
 * (e.g. ROLE_DAMS also yields ROLE_OWNERS, ROLE_ADMIN...). The frontend checks roles with a plain
 * includes(), so it must receive the inherited ones too. Only for the current user's own payload:
 * user management screens keep the raw stored roles.
 */
class EffectiveRoles
{
    public function __construct(private RoleHierarchyInterface $roleHierarchy)
    {
    }

    public function of(User $user): array
    {
        return array_values(array_unique($this->roleHierarchy->getReachableRoleNames($user->getRoles())));
    }
}
