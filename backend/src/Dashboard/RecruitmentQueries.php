<?php

namespace App\Dashboard;

use Doctrine\DBAL\Connection;

/**
 * Agrégations du dashboard recrutement, en SQL natif.
 *
 * Périmètre : chaque méthode reçoit $recruiterId. null = toute la guilde ; un entier = les
 * seules recrues de ce recruteur (périmètre « own » d'un Enroleur). Pour un Enroleur sans
 * personnage lié, le contrôleur passe RecruitmentQueries::NOBODY : il ne voit rien.
 *
 * Règle de rétention (cohorte mature) : une recrue n'entre dans le calcul d'un palier J+k
 * que si elle a atteint cet âge (recruited_at + k jours <= maintenant). Elle est « retenue »
 * si elle est toujours là, ou partie à J+k ou après. Les départs non datés (archivés avant
 * la mise en place du suivi) sont exclus, car on ne sait pas de quel côté du palier ils
 * tombent ; leur nombre est renvoyé à part. L'effectif pris en compte (n) est toujours renvoyé.
 */
class RecruitmentQueries
{
    public const MILESTONES = [14, 61, 122];
    /** Recruteur fictif qui ne correspond à personne (Enroleur sans personnage lié). */
    public const NOBODY = -1;
    /** Âge maximal (en jours, exclu) d'une recrue « à surveiller ». */
    public const WATCH_DAYS = 30;
    /** Tranches d'ancienneté au départ : libellé lisible, bornes en jours (incluses). */
    public const DEPARTURE_BUCKETS = [
        ["Moins d'1 semaine", 0, 7],
        ['1 à 2 semaines', 8, 14],
        ['2 semaines à 1 mois', 15, 30],
        ['1 à 2 mois', 31, 60],
        ['2 à 4 mois', 61, 121],
        ['4 mois à 1 an', 122, 364],
        ["Plus d'1 an", 365, null],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * KPI d'une semaine ISO (lundi → dimanche) : recrues, comparaison S-1 et moyenne des
     * 8 semaines précédentes, départs, solde et liste des recrues.
     */
    public function week(\DateTimeImmutable $weekStart, \DateTimeImmutable $now, ?int $recruiterId): array
    {
        $params = ['ws' => $this->ts($weekStart), 'rid' => $recruiterId];

        $row = $this->connection->fetchAssociative(<<<'SQL'
            WITH b AS (SELECT CAST(:ws AS timestamp) AS ws)
            SELECT
              COUNT(*) FILTER (WHERE c.recruited_at >= b.ws AND c.recruited_at < b.ws + INTERVAL '7 days')                     AS recruits,
              COUNT(*) FILTER (WHERE c.recruited_at >= b.ws - INTERVAL '7 days' AND c.recruited_at < b.ws)                     AS previous,
              COUNT(*) FILTER (WHERE c.recruited_at >= b.ws - INTERVAL '56 days' AND c.recruited_at < b.ws)                    AS last8,
              COUNT(*) FILTER (WHERE c.archived_at >= b.ws AND c.archived_at < b.ws + INTERVAL '7 days')                       AS departures
            FROM characters c, b
            WHERE (CAST(:rid AS int) IS NULL OR c.recruiter_id = CAST(:rid AS int))
        SQL, $params);

        $list = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT c.id, c.pseudo, c.class, c.recruited_at::date AS recruited_on, c.is_archived,
                   c.archived_at::date AS archived_on, rec.pseudo AS recruiter
            FROM characters c
            LEFT JOIN characters rec ON rec.id = c.recruiter_id
            WHERE c.recruited_at >= CAST(:ws AS timestamp)
              AND c.recruited_at < CAST(:ws AS timestamp) + INTERVAL '7 days'
              AND (CAST(:rid AS int) IS NULL OR c.recruiter_id = CAST(:rid AS int))
            ORDER BY c.recruited_at, c.pseudo
        SQL, $params);

        $recruits = (int) $row['recruits'];
        $previous = (int) $row['previous'];
        $average8 = round((int) $row['last8'] / 8, 1);
        $departures = (int) $row['departures'];
        $weekEnd = $weekStart->modify('+6 days');

        return [
            'week' => $weekStart->format('o-\WW'),
            'start' => $weekStart->format('Y-m-d'),
            'end' => $weekEnd->format('Y-m-d'),
            'isCurrent' => $now >= $weekStart && $now < $weekStart->modify('+7 days'),
            'recruits' => $recruits,
            'previous' => $previous,
            'deltaPrevious' => $recruits - $previous,
            'average8' => $average8,
            'deltaAverage8Percent' => $average8 > 0 ? (int) round(100 * ($recruits - $average8) / $average8) : null,
            'departures' => $departures,
            'net' => $recruits - $departures,
            'list' => array_map(fn (array $r) => [
                'characterId' => (int) $r['id'],
                'pseudo' => $r['pseudo'],
                'class' => $r['class'],
                'recruitedAt' => $r['recruited_on'],
                'recruiter' => $r['recruiter'],
                'stillHere' => !$r['is_archived'],
                'leftAt' => $r['archived_on'],
            ], $list),
        ];
    }

    /**
     * Par recruteur : volume, rétention aux paliers et position au classement (nombre de
     * recrues encore présentes à J+61). Le classement est toujours calculé sur tous les
     * recruteurs ; en périmètre restreint seule la ligne du recruteur est renvoyée.
     *
     * @param int $months fenêtre de recrutement prise en compte (0 = depuis toujours)
     */
    public function recruiters(\DateTimeImmutable $now, int $months, ?int $recruiterId): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            WITH p AS (
              SELECT CAST(:now AS timestamp) AS now,
                     CASE WHEN CAST(:months AS int) > 0
                          THEN CAST(:now AS timestamp) - make_interval(months => CAST(:months AS int))
                     END AS since
            ),
            recruits AS (
              SELECT c.*,
                     (c.is_archived AND c.archived_at IS NULL) AS undated,
                     (NOT c.is_archived OR c.archived_at IS NOT NULL) AS dated
              FROM characters c, p
              WHERE c.recruiter_id IS NOT NULL
                AND c.recruited_at <= p.now
                AND (p.since IS NULL OR c.recruited_at > p.since)
            ),
            stats AS (
              SELECT r.recruiter_id,
                     COUNT(*)                                       AS volume,
                     COUNT(*) FILTER (WHERE NOT r.is_archived)      AS still_here,
                     COUNT(*) FILTER (WHERE r.undated)              AS excluded,
                     COUNT(*) FILTER (WHERE r.dated AND r.recruited_at + INTERVAL '14 days' <= p.now)  AS n14,
                     COUNT(*) FILTER (WHERE r.dated AND r.recruited_at + INTERVAL '14 days' <= p.now
                                        AND (NOT r.is_archived OR r.archived_at >= r.recruited_at + INTERVAL '14 days'))  AS kept14,
                     COUNT(*) FILTER (WHERE r.dated AND r.recruited_at + INTERVAL '61 days' <= p.now)  AS n61,
                     COUNT(*) FILTER (WHERE r.dated AND r.recruited_at + INTERVAL '61 days' <= p.now
                                        AND (NOT r.is_archived OR r.archived_at >= r.recruited_at + INTERVAL '61 days'))  AS kept61,
                     COUNT(*) FILTER (WHERE r.dated AND r.recruited_at + INTERVAL '122 days' <= p.now) AS n122,
                     COUNT(*) FILTER (WHERE r.dated AND r.recruited_at + INTERVAL '122 days' <= p.now
                                        AND (NOT r.is_archived OR r.archived_at >= r.recruited_at + INTERVAL '122 days')) AS kept122
              FROM recruits r, p
              GROUP BY r.recruiter_id
            ),
            ranked AS (
              SELECT s.*,
                     RANK() OVER (ORDER BY s.kept61 DESC)  AS position,
                     COUNT(*) OVER ()                       AS total_recruiters
              FROM stats s
            )
            SELECT ranked.*, rec.pseudo, rec.class, rec.is_archived AS recruiter_archived
            FROM ranked
            JOIN characters rec ON rec.id = ranked.recruiter_id
            WHERE CAST(:rid AS int) IS NULL OR ranked.recruiter_id = CAST(:rid AS int)
            ORDER BY ranked.position, ranked.volume DESC, rec.pseudo
        SQL, ['now' => $this->ts($now), 'months' => $months, 'rid' => $recruiterId]);

        return array_map(function (array $r) {
            $milestones = [];
            foreach (self::MILESTONES as $k) {
                $milestones["d$k"] = $this->rate((int) $r["kept$k"], (int) $r["n$k"]);
            }

            return [
                'recruiterId' => (int) $r['recruiter_id'],
                'pseudo' => $r['pseudo'],
                'class' => $r['class'],
                'recruiterArchived' => (bool) $r['recruiter_archived'],
                'volume' => (int) $r['volume'],
                'stillHere' => (int) $r['still_here'],
                'excluded' => (int) $r['excluded'],
                'retention' => $milestones,
                'position' => (int) $r['position'],
                'totalRecruiters' => (int) $r['total_recruiters'],
            ];
        }, $rows);
    }

    /**
     * Recrues actives de moins de WATCH_DAYS jours ayant au moins un avertissement ou un
     * vote fantôme (toutes manches confondues).
     */
    public function watchlist(\DateTimeImmutable $now, ?int $recruiterId): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT * FROM (
              SELECT c.id, c.pseudo, c.class, c.recruited_at::date AS recruited_on,
                     (CAST(:today AS date) - c.recruited_at::date) AS days,
                     rec.pseudo AS recruiter,
                     (SELECT COUNT(*) FROM warnings w WHERE w.character_id = c.id) AS warnings,
                     (SELECT w.description FROM warnings w WHERE w.character_id = c.id
                       ORDER BY w.created_at DESC LIMIT 1)                       AS last_warning,
                     (SELECT COUNT(*) FROM ghost_vote v WHERE v.character_id = c.id) AS ghost_votes
              FROM characters c
              LEFT JOIN characters rec ON rec.id = c.recruiter_id
              WHERE NOT c.is_archived
                AND c.recruited_at <= CAST(:now AS timestamp)
                AND c.recruited_at::date > CAST(:today AS date) - CAST(:days AS int)
                AND (CAST(:rid AS int) IS NULL OR c.recruiter_id = CAST(:rid AS int))
            ) x
            WHERE x.warnings > 0 OR x.ghost_votes > 0
            ORDER BY (x.warnings + x.ghost_votes) DESC, x.days, x.pseudo
        SQL, [
            'now' => $this->ts($now),
            'today' => $now->format('Y-m-d'),
            'days' => self::WATCH_DAYS,
            'rid' => $recruiterId,
        ]);

        return array_map(fn (array $r) => [
            'characterId' => (int) $r['id'],
            'pseudo' => $r['pseudo'],
            'class' => $r['class'],
            'recruitedAt' => $r['recruited_on'],
            'days' => (int) $r['days'],
            'recruiter' => $r['recruiter'],
            'warnings' => (int) $r['warnings'],
            'lastWarning' => $r['last_warning'],
            'ghostVotes' => (int) $r['ghost_votes'],
        ], $rows);
    }

    /**
     * Cohortes mensuelles (mois d'arrivée) des $months derniers mois, mois courant inclus,
     * avec la rétention à chaque palier ; un palier sans recrue mature vaut null.
     */
    public function cohorts(\DateTimeImmutable $now, int $months, ?int $recruiterId): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            WITH p AS (SELECT CAST(:now AS timestamp) AS now),
            months AS (
              SELECT generate_series(
                date_trunc('month', CAST(:now AS timestamp)) - make_interval(months => CAST(:months AS int) - 1),
                date_trunc('month', CAST(:now AS timestamp)),
                INTERVAL '1 month'
              ) AS m
            )
            SELECT to_char(months.m, 'YYYY-MM') AS month,
                   COUNT(c.id) AS recruits,
                   COUNT(c.id) FILTER (WHERE c.is_archived AND c.archived_at IS NULL) AS excluded,
                   COUNT(c.id) FILTER (WHERE NOT c.is_archived) AS still_here,
                   COUNT(c.id) FILTER (WHERE (NOT c.is_archived OR c.archived_at IS NOT NULL)
                                         AND c.recruited_at + INTERVAL '14 days' <= p.now) AS n14,
                   COUNT(c.id) FILTER (WHERE (NOT c.is_archived OR c.archived_at IS NOT NULL)
                                         AND c.recruited_at + INTERVAL '14 days' <= p.now
                                         AND (NOT c.is_archived OR c.archived_at >= c.recruited_at + INTERVAL '14 days')) AS kept14,
                   COUNT(c.id) FILTER (WHERE (NOT c.is_archived OR c.archived_at IS NOT NULL)
                                         AND c.recruited_at + INTERVAL '61 days' <= p.now) AS n61,
                   COUNT(c.id) FILTER (WHERE (NOT c.is_archived OR c.archived_at IS NOT NULL)
                                         AND c.recruited_at + INTERVAL '61 days' <= p.now
                                         AND (NOT c.is_archived OR c.archived_at >= c.recruited_at + INTERVAL '61 days')) AS kept61,
                   COUNT(c.id) FILTER (WHERE (NOT c.is_archived OR c.archived_at IS NOT NULL)
                                         AND c.recruited_at + INTERVAL '122 days' <= p.now) AS n122,
                   COUNT(c.id) FILTER (WHERE (NOT c.is_archived OR c.archived_at IS NOT NULL)
                                         AND c.recruited_at + INTERVAL '122 days' <= p.now
                                         AND (NOT c.is_archived OR c.archived_at >= c.recruited_at + INTERVAL '122 days')) AS kept122
            FROM months
            CROSS JOIN p
            LEFT JOIN characters c
                   ON c.recruited_at >= months.m
                  AND c.recruited_at < months.m + INTERVAL '1 month'
                  AND c.recruited_at <= p.now
                  AND (CAST(:rid AS int) IS NULL OR c.recruiter_id = CAST(:rid AS int))
            GROUP BY months.m
            ORDER BY months.m DESC
        SQL, ['now' => $this->ts($now), 'months' => $months, 'rid' => $recruiterId]);

        return array_map(function (array $r) {
            $milestones = [];
            foreach (self::MILESTONES as $k) {
                $milestones["d$k"] = $this->rate((int) $r["kept$k"], (int) $r["n$k"]);
            }

            return [
                'month' => $r['month'],
                'recruits' => (int) $r['recruits'],
                'stillHere' => (int) $r['still_here'],
                'excluded' => (int) $r['excluded'],
                'retention' => $milestones,
            ];
        }, $rows);
    }

    /**
     * Répartition du temps passé dans la guilde par les membres partis (départs datés) dont
     * le départ tombe dans la fenêtre ($months derniers mois, 0 = depuis toujours).
     */
    public function departureTimes(\DateTimeImmutable $now, int $months, ?int $recruiterId): array
    {
        $cases = [];
        foreach (self::DEPARTURE_BUCKETS as $i => [, $from, $to]) {
            $cases[] = $to === null ? "WHEN d.days >= $from THEN $i" : "WHEN d.days BETWEEN $from AND $to THEN $i";
        }
        $bucketCase = 'CASE ' . implode(' ', $cases) . ' END';

        $sql = <<<SQL
            WITH p AS (
              SELECT CAST(:now AS timestamp) AS now,
                     CASE WHEN CAST(:months AS int) > 0
                          THEN CAST(:now AS timestamp) - make_interval(months => CAST(:months AS int))
                     END AS since
            ),
            d AS (
              SELECT (c.archived_at::date - c.recruited_at::date) AS days
              FROM characters c, p
              WHERE c.is_archived AND c.archived_at IS NOT NULL
                AND c.archived_at <= p.now
                AND (p.since IS NULL OR c.archived_at > p.since)
                AND (CAST(:rid AS int) IS NULL OR c.recruiter_id = CAST(:rid AS int))
            )
            SELECT $bucketCase AS bucket, COUNT(*) AS count,
                   (SELECT percentile_cont(0.5) WITHIN GROUP (ORDER BY days) FROM d) AS median,
                   (SELECT COUNT(*) FROM d) AS total
            FROM d
            GROUP BY bucket
        SQL;
        $rows = $this->connection->fetchAllAssociative($sql, ['now' => $this->ts($now), 'months' => $months, 'rid' => $recruiterId]);

        $counts = array_fill(0, count(self::DEPARTURE_BUCKETS), 0);
        $median = null;
        $total = 0;
        foreach ($rows as $r) {
            $counts[(int) $r['bucket']] = (int) $r['count'];
            $median = $r['median'] !== null ? (float) $r['median'] : null;
            $total = (int) $r['total'];
        }

        $excluded = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM characters WHERE is_archived AND archived_at IS NULL
               AND (CAST(:rid AS int) IS NULL OR recruiter_id = CAST(:rid AS int))',
            ['rid' => $recruiterId]
        );

        return [
            'buckets' => array_map(
                fn ($b, $i) => ['label' => $b[0], 'from' => $b[1], 'to' => $b[2], 'count' => $counts[$i]],
                self::DEPARTURE_BUCKETS,
                array_keys(self::DEPARTURE_BUCKETS)
            ),
            'total' => $total,
            'medianDays' => $median,
            'undatedExcluded' => $excluded,
        ];
    }

    /** @return array{rate: ?int, retained: int, n: int} */
    private function rate(int $retained, int $n): array
    {
        return ['rate' => $n > 0 ? (int) round(100 * $retained / $n) : null, 'retained' => $retained, 'n' => $n];
    }

    private function ts(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
