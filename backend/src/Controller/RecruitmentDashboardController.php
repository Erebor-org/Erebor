<?php

namespace App\Controller;

use App\Dashboard\RecruitmentQueries;
use App\Entity\User;
use App\Repository\CharactersRepository;
use App\Security\RecruitmentAccess;
use App\Service\AppSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Dashboard recrutement. Le périmètre visible (toute la guilde, ou les seules recrues de
 * l'Enroleur connecté) est résolu par RecruitmentAccess et appliqué dans chaque requête SQL :
 * rien de ce que l'utilisateur ne doit pas voir ne quitte le serveur.
 *
 * GET /recruitment/dashboard?week=2026-W48&months=12
 *   week   : semaine ISO des KPI (défaut : semaine en cours)
 *   months : fenêtre des stats par recruteur et des départs (3, 6, 12 ou 0 = depuis toujours)
 */
class RecruitmentDashboardController extends AbstractController
{
    private const ALLOWED_MONTHS = [3, 6, 12, 0];
    private const DEFAULT_MONTHS = 12;
    private const COHORT_MONTHS = 12;

    #[Route('/recruitment/dashboard', name: 'recruitment_dashboard', methods: ['GET'])]
    public function dashboard(
        Request $request,
        RecruitmentAccess $access,
        RecruitmentQueries $queries,
        CharactersRepository $charactersRepository,
        AppSettings $settings,
    ): JsonResponse {
        $user = $this->getUser();
        $scope = $access->resolve($user instanceof User ? $user : null);
        if ($scope === null) {
            return $this->json(['error' => 'Accès au dashboard recrutement refusé.'], 403);
        }

        $now = new \DateTimeImmutable();
        $weekStart = $this->parseWeek($request->query->get('week'), $now);
        if ($weekStart === null) {
            return $this->json(['error' => 'Paramètre week invalide (format attendu : 2026-W48).'], 400);
        }
        $months = $request->query->getInt('months', self::DEFAULT_MONTHS);
        if (!in_array($months, self::ALLOWED_MONTHS, true)) {
            return $this->json(['error' => 'Paramètre months invalide (3, 6, 12 ou 0).'], 400);
        }

        $restricted = $scope['scope'] === RecruitmentAccess::OWN;
        $rid = $restricted ? ($scope['recruiterId'] ?? RecruitmentQueries::NOBODY) : null;
        $recruiters = $queries->recruiters($now, $months, $rid);
        $me = $scope['recruiterId'] ? $charactersRepository->find($scope['recruiterId']) : null;

        return $this->json([
            'generatedAt' => $now->format(\DATE_ATOM),
            'access' => [
                'scope' => $scope['scope'],
                'recruiter' => $restricted && $me ? ['id' => $me->getId(), 'pseudo' => $me->getPseudo()] : null,
                'canConfigure' => $this->isGranted('ROLE_OWNERS'),
                'adminScope' => $settings->get(AppSettings::RECRUITMENT_ADMIN_SCOPE),
            ],
            'filters' => ['week' => $weekStart->format('o-\WW'), 'months' => $months],
            'week' => $queries->week($weekStart, $now, $rid),
            'recruiters' => $recruiters,
            // En périmètre restreint : uniquement la position de l'Enroleur, pas les autres noms.
            'leaderboard' => $restricted
                ? ['mine' => $recruiters[0] ?? null, 'entries' => null]
                : ['mine' => null, 'entries' => $recruiters],
            'watchlist' => $queries->watchlist($now, $rid),
            'cohorts' => $queries->cohorts($now, self::COHORT_MONTHS, $rid),
            'departures' => $queries->departureTimes($now, $months, $rid),
            'milestones' => RecruitmentQueries::MILESTONES,
            'watchDays' => RecruitmentQueries::WATCH_DAYS,
        ]);
    }

    /** Lundi 00:00 de la semaine ISO demandée (ou de la semaine en cours). */
    private function parseWeek(?string $week, \DateTimeImmutable $now): ?\DateTimeImmutable
    {
        if ($week === null || $week === '') {
            return $now->modify('monday this week')->setTime(0, 0);
        }
        if (!preg_match('/^(\d{4})-W(\d{2})$/', $week, $m) || (int) $m[2] < 1 || (int) $m[2] > 53) {
            return null;
        }
        $monday = (new \DateTimeImmutable())->setISODate((int) $m[1], (int) $m[2])->setTime(0, 0);

        return $monday->format('o-\WW') === $week ? $monday : null;
    }
}
