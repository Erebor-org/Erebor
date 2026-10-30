<?php

namespace App\Controller;

use App\Entity\Gw2LegendaryTracking;
use App\Entity\User;
use App\Repository\Gw2LegendaryTrackingRepository;
use App\Service\Gw2\Gw2LegendaryCatalog;
use App\Service\Gw2\Gw2LegendaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Legendary tracking in the private Guild Wars 2 section. ROLE_DAMS only (also enforced by
 * access_control on ^/gw2). Every read accepts ?refresh=1 to bypass the 5 minute cache.
 */
#[IsGranted('ROLE_DAMS')]
class Gw2LegendaryController extends AbstractController
{
    use Gw2ErrorResponses;

    public function __construct(
        private Gw2LegendaryService $legendaries,
        private Gw2LegendaryTrackingRepository $trackings,
    ) {
    }

    #[Route('/gw2/legendaries/catalog', name: 'gw2_legendaries_catalog', methods: ['GET'])]
    public function catalog(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        return $this->handle(fn () => $this->legendaries->catalog(
            $this->trackings->findItemIdsForUser($user),
            $request->query->getBoolean('refresh'),
        ));
    }

    #[Route('/gw2/legendaries/tracked', name: 'gw2_legendaries_tracked', methods: ['GET'])]
    public function tracked(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        return $this->handle(fn () => $this->legendaries->tracked(
            $this->trackings->findItemIdsForUser($user),
            $request->query->getBoolean('refresh'),
        ));
    }

    #[Route('/gw2/legendaries/tracked', name: 'gw2_legendaries_track', methods: ['POST'])]
    public function track(Request $request, #[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {
        $itemId = (int) (json_decode($request->getContent(), true)['itemId'] ?? 0);
        if (!Gw2LegendaryCatalog::has($itemId)) {
            return $this->json(['error' => 'Unknown legendary'], 400);
        }

        if (!$this->trackings->findOneBy(['user' => $user, 'itemId' => $itemId])) {
            $em->persist(new Gw2LegendaryTracking($user, $itemId));
            $em->flush();
        }

        return $this->json(['tracked' => $this->trackings->findItemIdsForUser($user)]);
    }

    #[Route('/gw2/legendaries/tracked/{itemId}', name: 'gw2_legendaries_untrack', methods: ['DELETE'], requirements: ['itemId' => '\d+'])]
    public function untrack(int $itemId, #[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {
        $tracking = $this->trackings->findOneBy(['user' => $user, 'itemId' => $itemId]);
        if ($tracking) {
            $em->remove($tracking);
            $em->flush();
        }

        return $this->json(['tracked' => $this->trackings->findItemIdsForUser($user)]);
    }

    #[Route('/gw2/legendaries/combined', name: 'gw2_legendaries_combined', methods: ['GET'])]
    public function combined(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        return $this->handle(fn () => $this->legendaries->combined(
            $this->trackings->findItemIdsForUser($user),
            $request->query->getBoolean('refresh'),
        ));
    }

    #[Route('/gw2/legendaries/{itemId}', name: 'gw2_legendary', methods: ['GET'], requirements: ['itemId' => '\d+'])]
    public function detail(int $itemId, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        if (!Gw2LegendaryCatalog::has($itemId)) {
            return $this->json(['error' => 'Unknown legendary', 'code' => 'not_found'], 404);
        }

        return $this->handle(fn () => $this->legendaries->detail($itemId, $request->query->getBoolean('refresh')) + [
            'tracked' => in_array($itemId, $this->trackings->findItemIdsForUser($user), true),
        ]);
    }
}
