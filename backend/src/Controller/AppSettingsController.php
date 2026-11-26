<?php

namespace App\Controller;

use App\Service\AppSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Réglages applicatifs. OWNERS uniquement (aussi imposé par access_control sur ^/admin/settings).
 */
#[IsGranted('ROLE_OWNERS')]
class AppSettingsController extends AbstractController
{
    #[Route('/admin/settings', name: 'app_settings_list', methods: ['GET'])]
    public function list(AppSettings $settings): JsonResponse
    {
        return $this->json([
            'settings' => $settings->all(),
            'allowed' => $settings->allowedValues(),
        ]);
    }

    /** Corps attendu : {"recruitment.admin_scope": "own", ...} — seules les clés envoyées changent. */
    #[Route('/admin/settings', name: 'app_settings_update', methods: ['PUT'])]
    public function update(Request $request, AppSettings $settings): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || $data === []) {
            return $this->json(['error' => 'Corps JSON attendu : un objet {clé: valeur}.'], 400);
        }

        try {
            foreach ($data as $key => $value) {
                $settings->set((string) $key, $value);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['settings' => $settings->all()]);
    }
}
