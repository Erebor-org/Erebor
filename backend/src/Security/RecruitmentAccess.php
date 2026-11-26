<?php

namespace App\Security;

use App\Entity\User;
use App\Service\AppSettings;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Qui voit quoi dans le dashboard recrutement. Toute la restriction se fait ici, côté
 * serveur : les requêtes reçoivent le périmètre résolu et filtrent en SQL.
 *
 *  - ROLE_SUPER_ADMIN et plus : tout ;
 *  - ROLE_ADMIN (Enroleur) : selon le réglage `recruitment.admin_scope` —
 *      none → pas d'accès,
 *      own  → uniquement ses propres recrues, et seulement sa position au classement,
 *      all  → tout ;
 *  - en dessous : pas d'accès.
 */
class RecruitmentAccess
{
    public const ALL = 'all';
    public const OWN = 'own';

    public function __construct(
        private readonly Security $security,
        private readonly AppSettings $settings,
    ) {
    }

    /**
     * @return array{scope: string, recruiterId: ?int}|null null = accès refusé ; en périmètre
     *         « own », recruiterId est le personnage de l'utilisateur (null s'il n'en a pas :
     *         il ne verra alors rien, sans voir les recrues des autres).
     */
    public function resolve(?User $user): ?array
    {
        if (!$user) {
            return null;
        }
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return ['scope' => self::ALL, 'recruiterId' => null];
        }
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return null;
        }

        return match ($this->settings->get(AppSettings::RECRUITMENT_ADMIN_SCOPE)) {
            AppSettings::SCOPE_ALL => ['scope' => self::ALL, 'recruiterId' => null],
            AppSettings::SCOPE_OWN => ['scope' => self::OWN, 'recruiterId' => $user->getCharacterId()],
            default => null,
        };
    }
}
