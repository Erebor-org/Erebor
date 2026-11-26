<?php

namespace App\Controller;

use App\Dashboard\HomeDashboardQueries;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Données de la page d'accueil. Tout membre connecté y a accès ; les blocs de gestion
 * (vote fantômes, recrues récentes) ne sont calculés et renvoyés que pour ROLE_ADMIN et plus.
 */
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    private const FEED_PAGE_SIZE = 20;
    private const FEED_MAX_PAGE_SIZE = 50;
    private const PROMOTIONS_SIZE = 30;
    private const FEED_KINDS = [
        'movements' => HomeDashboardQueries::FEED_MOVEMENTS,
        'promotions' => HomeDashboardQueries::FEED_PROMOTIONS,
    ];

    #[Route('/dashboard/home', name: 'dashboard_home', methods: ['GET'])]
    public function home(HomeDashboardQueries $queries): JsonResponse
    {
        $user = $this->getUser();
        $myCharacterId = $user instanceof User ? $user->getCharacterId() : null;
        $now = new \DateTimeImmutable();

        $data = [
            'generatedAt' => $now->format(\DATE_ATOM),
            'counters' => $queries->counters($now, $myCharacterId),
            'retention61' => $queries->retention61($now),
            'mySeniority' => $queries->mySeniorityRank($now, $myCharacterId),
            'monthly' => $queries->monthlyFlow($now),
            'classes' => $queries->classDistribution(),
            'upcomingPromotions' => $queries->upcomingPromotions($now),
            'myProgress' => $queries->myProgress($now, $myCharacterId),
            'movements' => $queries->activity($now, HomeDashboardQueries::FEED_MOVEMENTS, self::FEED_PAGE_SIZE),
            'recentPromotions' => $queries->activity($now, HomeDashboardQueries::FEED_PROMOTIONS, self::PROMOTIONS_SIZE),
            'admin' => null,
        ];

        if ($this->isGranted('ROLE_ADMIN') && $user instanceof User) {
            $data['admin'] = [
                'ghost' => $queries->ghostRoundStatus($user->getId()),
                'freshRecruits' => $queries->freshRecruits($now),
            ];
        }

        return $this->json($data);
    }

    /** Pages suivantes d'un fil : ?kind=movements|promotions&offset=20&limit=20 */
    #[Route('/dashboard/activity', name: 'dashboard_activity', methods: ['GET'])]
    public function activity(Request $request, HomeDashboardQueries $queries): JsonResponse
    {
        $kind = $request->query->get('kind', 'movements');
        if (!isset(self::FEED_KINDS[$kind])) {
            return $this->json(['error' => 'kind attendu : ' . implode(' ou ', array_keys(self::FEED_KINDS))], 400);
        }
        $limit = min(self::FEED_MAX_PAGE_SIZE, max(1, $request->query->getInt('limit', self::FEED_PAGE_SIZE)));
        $offset = max(0, $request->query->getInt('offset'));
        $items = $queries->activity(new \DateTimeImmutable(), self::FEED_KINDS[$kind], $limit + 1, $offset);

        return $this->json([
            'items' => array_slice($items, 0, $limit),
            'hasMore' => count($items) > $limit,
        ]);
    }
}
