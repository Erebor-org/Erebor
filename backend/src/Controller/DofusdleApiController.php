<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\DofusdleMonster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ClassicComparator;

class DofusdleApiController extends AbstractController
{
    private ClassicComparator $comparator;
    private string $dailyMonIdKey = 'dofusdle_classic_daily_monster_id';
    public function __construct(ClassicComparator $comparator) { $this->comparator = $comparator; }

    #[Route('/erebor/dofusdle/api/monsters', name: 'dofusdle_api_monsters', methods: ['GET'])]
    public function monsters(EntityManagerInterface $em): JsonResponse
    {
        $monsters = $em->getRepository(DofusdleMonster::class)->findAll();
        $data = array_map(function (DofusdleMonster $m) {
            return [
                'id' => $m->getId(),
                'dofusdb_id' => $m->getDofusdbId(),
                'name' => $m->getName(),
                'pdv' => $m->getPdv(),
                'pa' => $m->getPa(),
                'pm' => $m->getPm(),
                'family' => $m->getFamily(),
                'level' => $m->getLevel(),
                'isBoss' => $m->isBoss(),
                'element' => $m->getElement(),
                'image' => $m->getImage(),
            ];
        }, $monsters);
        return $this->json($data);
    }

    private function getOrSetDailyMonsterId(Request $request, EntityManagerInterface $em): ?int
    {
        $session = $request->getSession();
        $dailyId = $session->get($this->dailyMonIdKey);
        if ($dailyId) {
            return $dailyId;
        }
        $monsters = $em->getRepository(DofusdleMonster::class)->findAll();
        if (!$monsters) return null;
        $rand = random_int(0, count($monsters) - 1);
        $dailyId = $monsters[$rand]->getDofusdbId();
        $session->set($this->dailyMonIdKey, $dailyId);
        return $dailyId;
    }

    #[Route('/erebor/dofusdle/api/classic/daily', name: 'dofusdle_classic_daily', methods: ['GET'])]
    public function daily(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $dailyId = $this->getOrSetDailyMonsterId($request, $em);
        if (!$dailyId) {
            return $this->json(['error' => 'No monsters'], 404);
        }
        $monster = $em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $dailyId]);
        if (!$monster) {
            return $this->json(['error' => 'Monster not found'], 404);
        }
        $puzzleId = 'classic_' . $dailyId;
        // Retourne toutes les données du monstre
        $monsterData = [
            'id' => $monster->getId(),
            'dofusdb_id' => $monster->getDofusdbId(),
            'name' => $monster->getName(),
            'pdv' => $monster->getPdv(),
            'pa' => $monster->getPa(),
            'pm' => $monster->getPm(),
            'family' => $monster->getFamily(),
            'level' => $monster->getLevel(),
            'isBoss' => $monster->isBoss(),
            'element' => $monster->getElement(),
            'image' => $monster->getImage(),
            'super_race' => $monster->getSuperRace(),
            'subareas' => $monster->getSubareas(),
            'areas' => $monster->getAreas(),
            'favorite_subarea_id' => $monster->getFavoriteSubareaId(),
            'boss_type' => $monster->getBossType(),
            'tags' => $monster->getTags(),
            'spells_count' => $monster->getSpellsCount(),
            'corresponding_mini_boss_id' => $monster->getCorrespondingMiniBossId(),
            'corresponding_mini_boss_name' => $monster->getCorrespondingMiniBossName(),
        ];
        return $this->json([
            'mode' => 'classic',
            'date' => (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))->format('Y-m-d'),
            'puzzleId' => $puzzleId,
            'solutionHash' => hash('sha256', $monster->getDofusdbId() . $puzzleId),
            'fields' => ['family','zone','level','rank','element','ap','mp','hp','resistDominant'],
            'monster' => $monsterData
        ]);
    }

    #[Route('/erebor/dofusdle/api/classic/regen-daily', name: 'dofusdle_classic_regen_daily', methods: ['POST'])]
    public function regenDaily(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $session = $request->getSession();
        $monsters = $em->getRepository(DofusdleMonster::class)->findAll();
        if (!$monsters) {
            return $this->json(['error' => 'No monsters'], 404);
        }
        $rand = random_int(0, count($monsters) - 1);
        $dailyId = $monsters[$rand]->getDofusdbId();
        $session->set($this->dailyMonIdKey, $dailyId);
        return $this->json(['success' => true, 'dofusdb_id' => $dailyId]);
    }

    #[Route('/erebor/dofusdle/api/classic/search', name: 'dofusdle_classic_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = $request->query->get('q', '');
        $repo = $em->getRepository(DofusdleMonster::class);
        $qb = $repo->createQueryBuilder('m')
            ->where('LOWER(m.name) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->setMaxResults(20);
        $results = $qb->getQuery()->getResult();
        $data = array_map(fn(DofusdleMonster $m) => [
            'id' => $m->getDofusdbId(),
            'name' => $m->getName(),
            'level' => $m->getLevel(),
            'img' => $m->getImage(),
        ], $results);
        return $this->json($data);
    }

    #[Route('/erebor/dofusdle/api/classic/guess', name: 'dofusdle_classic_guess', methods: ['POST'])]
    public function guess(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $guessId = $data['guessId'] ?? null;
        $session = $request->getSession();
        $dailyId = $session->get($this->dailyMonIdKey);
        if (!$dailyId) {
            // Génère un daily à la volée si absent
            $monsters = $em->getRepository(DofusdleMonster::class)->findAll();
            if (!$monsters) {
                return $this->json(['error' => 'No monsters'], 404);
            }
            $rand = random_int(0, count($monsters) - 1);
            $dailyId = $monsters[$rand]->getDofusdbId();
            $session->set($this->dailyMonIdKey, $dailyId);
        }
        $solution = $em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $dailyId]);
        $guess = $em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $guessId]);
        if (!$guess || !$solution) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $correct = $guess->getDofusdbId() === $solution->getDofusdbId();
        // Log debug
        $log = sprintf(
            "guessId: %s, dailyId: %s, guess->getDofusdbId(): %s, solution->getDofusdbId(): %s, correct: %s\n",
            $guessId,
            $dailyId,
            $guess->getDofusdbId(),
            $solution->getDofusdbId(),
            $correct ? 'true' : 'false'
        );
        file_put_contents('/tmp/dofusdle_debug.log', $log, FILE_APPEND);
        $hint = $this->comparator->compare($guess, $solution);
        return $this->json([
            'correct' => $correct,
            'hint' => $hint,
            'dailyId' => $solution->getDofusdbId(),
        ]);
    }

    #[Route('/erebor/dofusdle/api/classic/compare-debug', name: 'dofusdle_classic_compare_debug', methods: ['POST'])]
    public function compareDebug(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dailyId = $data['dailyId'] ?? null;
        $guessId = $data['guessId'] ?? null;
        $daily = $em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $dailyId]);
        $guess = $em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $guessId]);
        if (!$daily || !$guess) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $fields = [
            'dofusdb_id','name','pdv','pa','pm','family','level','isBoss','element','image','super_race','subareas','areas','favorite_subarea_id','boss_type','tags','spells_count','corresponding_mini_boss_id','corresponding_mini_boss_name'
        ];
        $dailyData = [];
        $guessData = [];
        $diffs = [];
        foreach ($fields as $f) {
            $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $f)));
            $dailyVal = method_exists($daily, $getter) ? $daily->$getter() : null;
            $guessVal = method_exists($guess, $getter) ? $guess->$getter() : null;
            $dailyData[$f] = $dailyVal;
            $guessData[$f] = $guessVal;
            if ($dailyVal != $guessVal) {
                $diffs[$f] = ['daily' => $dailyVal, 'guess' => $guessVal];
            }
        }
        $hint = $this->comparator->compare($guess, $daily);
        return $this->json([
            'daily' => $dailyData,
            'guess' => $guessData,
            'hint' => $hint,
            'diffs' => $diffs,
        ]);
    }
}
