<?php

namespace App\Command;

use App\Entity\Characters;
use App\Entity\GuildEvent;
use App\EventListener\GuildEventListener;
use App\Repository\RanksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Données de démonstration pour les tableaux de bord : des recrues réparties sur plusieurs
 * mois, dont une partie repart à des dates réalistes (beaucoup de départs les premières
 * semaines, de moins en moins ensuite). Chaque départ a sa date d'archivage et son événement
 * « left » daté, comme s'il avait été saisi à l'époque.
 *
 * Tout ce qui est généré est marqué (notes = MARKER) : `--purge` le supprime entièrement.
 * Refuse de tourner en prod.
 *
 *   php bin/console app:demo:movements                 # 12 mois, ~8 recrues / mois
 *   php bin/console app:demo:movements --months=18 --per-month=10 --seed=7
 *   php bin/console app:demo:movements --purge
 */
#[AsCommand(name: 'app:demo:movements', description: 'Génère (ou purge) des arrivées et départs de démonstration sur plusieurs mois')]
class DemoMovementsCommand extends Command
{
    public const MARKER = '[démo] généré par app:demo:movements';

    private const CLASSES = [
        'iop', 'cra', 'eniripsa', 'sram', 'xelor', 'ecaflip', 'enutrof', 'sadida', 'osamodas', 'feca',
        'sacrieur', 'pandawa', 'roublard', 'zobal', 'steamer', 'eliotrope', 'huppermage', 'ouginak', 'forgelance',
    ];

    private const SYLLABLES = ['ka', 'ro', 'mi', 'tal', 'dor', 'en', 'lya', 'vo', 'ran', 'is', 'thu', 'gar', 'el', 'zan', 'qui', 'mor', 'sa', 'bri', 'ul', 'fen'];

    /**
     * Probabilité de partir dans chaque tranche d'ancienneté (en jours), pour une recrue
     * encore présente au début de la tranche. Donne ~45 % de départs avant J+122.
     */
    private const DEPARTURE_HAZARD = [
        [1, 14, 0.22],
        [14, 61, 0.15],
        [61, 122, 0.12],
        [122, 365, 0.15],
        [365, 730, 0.10],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RanksRepository $ranksRepository,
        private readonly GuildEventListener $guildEventListener,
        #[Autowire('%kernel.environment%')] private readonly string $environment,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('months', null, InputOption::VALUE_REQUIRED, 'Nombre de mois couverts (mois courant inclus)', '12')
            ->addOption('per-month', null, InputOption::VALUE_REQUIRED, 'Recrues par mois en moyenne', '8')
            ->addOption('seed', null, InputOption::VALUE_REQUIRED, 'Graine aléatoire (même graine = mêmes données)', '42')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Supprime toutes les données de démonstration et s\'arrête');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->environment === 'prod') {
            $io->error('Commande de démonstration : interdite en prod.');
            return Command::FAILURE;
        }

        if ($input->getOption('purge')) {
            $removed = $this->purge();
            $io->success(sprintf('%d personnage(s) de démonstration supprimé(s), avec leurs événements.', $removed));
            return Command::SUCCESS;
        }

        $months = max(1, (int) $input->getOption('months'));
        $perMonth = max(1, (int) $input->getOption('per-month'));
        mt_srand((int) $input->getOption('seed'));

        $existing = (int) $this->em->getConnection()->fetchOne('SELECT COUNT(*) FROM characters WHERE notes = ?', [self::MARKER]);
        if ($existing > 0) {
            $io->warning(sprintf('%d personnage(s) de démonstration existent déjà : lancez --purge d\'abord pour repartir de zéro.', $existing));
            return Command::FAILURE;
        }

        $recruiters = $this->recruiters();
        if ($recruiters === []) {
            $io->error('Aucun recruteur actif (rang avec recruiter = true) pour rattacher les recrues.');
            return Command::FAILURE;
        }

        $now = new \DateTimeImmutable();
        $stats = $this->guildEventListener->withoutRecording(
            fn () => $this->generate($now, $months, $perMonth, $recruiters)
        );

        $io->success(sprintf(
            '%d recrues générées sur %d mois, dont %d reparties (événements « left » datés). Purge : app:demo:movements --purge',
            $stats['recruits'],
            $months,
            $stats['departures']
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<Characters> $recruiters
     * @return array{recruits: int, departures: int}
     */
    private function generate(\DateTimeImmutable $now, int $months, int $perMonth, array $recruiters): array
    {
        $firstMonth = $now->modify('first day of this month')->setTime(0, 0)->modify(sprintf('-%d months', $months - 1));
        $usedPseudos = [];
        $recruits = 0;
        $departures = 0;

        for ($m = 0; $m < $months; $m++) {
            $monthStart = $firstMonth->modify("+$m months");
            $isCurrentMonth = $monthStart->format('Y-m') === $now->format('Y-m');
            $lastDay = $isCurrentMonth ? (int) $now->format('j') : (int) $monthStart->format('t');
            // Saisonnalité douce : plus de monde en été et en hiver, creux au printemps / automne.
            $season = 1 + 0.35 * cos(2 * M_PI * ((int) $monthStart->format('n') - 7) / 6);
            $count = max(1, (int) round($perMonth * $season * $this->jitter(0.7, 1.3) * ($lastDay / (int) $monthStart->format('t'))));

            for ($i = 0; $i < $count; $i++) {
                $recruitedAt = $monthStart->modify(sprintf('+%d days', mt_rand(0, $lastDay - 1)));
                $leftAt = $this->departureDate($recruitedAt, $now);
                $seniorityEnd = $leftAt ?? $now;
                $days = max(1, (int) $recruitedAt->diff($seniorityEnd)->days);
                $rank = $this->ranksRepository->findClassicRankForDays($days);
                $recruiter = $recruiters[mt_rand(0, count($recruiters) - 1)];

                $character = (new Characters())
                    ->setPseudo($this->pseudo($usedPseudos))
                    ->setClass(self::CLASSES[mt_rand(0, count(self::CLASSES) - 1)])
                    ->setRecruitedAt(\DateTime::createFromImmutable($recruitedAt))
                    ->setRank($rank)
                    ->setRecruiter($recruiter)
                    ->setNotes(self::MARKER);
                $character->setAnkamaPseudo($character->getPseudo() . '#' . mt_rand(1000, 9999));
                $this->em->persist($character);
                $recruits++;

                if ($leftAt !== null) {
                    $character->setIsArchived(true);
                    $character->setArchivedAt(\DateTime::createFromImmutable($leftAt));
                    $this->em->persist(
                        (new GuildEvent(GuildEvent::LEFT, $character, \DateTime::createFromImmutable($leftAt)))
                            ->setPayload([
                                'class' => $character->getClass(),
                                'rank' => $rank->getName(),
                                'recruitedAt' => $recruitedAt->format('Y-m-d'),
                                'daysInGuild' => $days,
                                'recruiter' => $recruiter->getPseudo(),
                                'demo' => true,
                            ])
                    );
                    $departures++;
                }
            }
            $this->em->flush();
        }

        return ['recruits' => $recruits, 'departures' => $departures];
    }

    /** Date de départ tirée selon DEPARTURE_HAZARD, ou null si la recrue est toujours là aujourd'hui. */
    private function departureDate(\DateTimeImmutable $recruitedAt, \DateTimeImmutable $now): ?\DateTimeImmutable
    {
        foreach (self::DEPARTURE_HAZARD as [$from, $to, $probability]) {
            if ($this->random() >= $probability) {
                continue;
            }
            $leftAt = $recruitedAt
                ->modify(sprintf('+%d days', mt_rand($from, $to - 1)))
                ->setTime(mt_rand(17, 23), mt_rand(0, 59));

            return $leftAt <= $now ? $leftAt : null;
        }

        return null;
    }

    /** @return list<Characters> recruteurs actifs (rang recruteur) */
    private function recruiters(): array
    {
        return $this->em->getRepository(Characters::class)->createQueryBuilder('c')
            ->join('c.rank', 'r')
            ->where('r.recruiter = true')
            ->andWhere('c.isArchived = false')
            ->andWhere('c.notes IS NULL OR c.notes <> :marker')
            ->setParameter('marker', self::MARKER)
            ->orderBy('c.id')
            ->getQuery()
            ->getResult();
    }

    private function purge(): int
    {
        $connection = $this->em->getConnection();

        return $connection->transactional(function () use ($connection): int {
            // Les événements passeraient à character_id NULL (ON DELETE SET NULL) : on les retire d'abord.
            $connection->executeStatement(
                'DELETE FROM guild_event WHERE character_id IN (SELECT id FROM characters WHERE notes = :marker)',
                ['marker' => self::MARKER]
            );
            $connection->executeStatement(
                'UPDATE characters SET recruiter_id = NULL WHERE recruiter_id IN (SELECT id FROM characters WHERE notes = :marker)',
                ['marker' => self::MARKER]
            );

            return (int) $connection->executeStatement('DELETE FROM characters WHERE notes = :marker', ['marker' => self::MARKER]);
        });
    }

    /** @param array<string, true> $used */
    private function pseudo(array &$used): string
    {
        do {
            $name = '';
            for ($i = 0, $n = mt_rand(2, 3); $i < $n; $i++) {
                $name .= self::SYLLABLES[mt_rand(0, count(self::SYLLABLES) - 1)];
            }
            $name = ucfirst($name);
        } while (isset($used[$name]));
        $used[$name] = true;

        return $name;
    }

    private function random(): float
    {
        return mt_rand() / mt_getrandmax();
    }

    private function jitter(float $min, float $max): float
    {
        return $min + ($max - $min) * $this->random();
    }
}
