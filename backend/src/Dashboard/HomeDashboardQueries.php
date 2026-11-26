<?php

namespace App\Dashboard;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Agrégations de la page d'accueil, en SQL natif (une requête par bloc).
 *
 * Conventions partagées :
 *  - « maintenant » est toujours passé en paramètre (testable, et cohérent dans une même réponse) ;
 *  - un personnage est actif à l'instant t s'il était recruté à t et pas encore parti :
 *    recruited_at <= t AND (NOT is_archived OR archived_at > t). Un archivé sans date
 *    (« départ antérieur au suivi ») est considéré parti depuis toujours ;
 *  - seuls les personnages principaux comptent (les mules sont à part) ;
 *  - rang d'ancienneté atteint à recruited_at + required_days (même règle que la création
 *    d'un personnage), en ne considérant que les rangs classiques (lead = false et
 *    required_days renseigné). Les membres portant un rang de fonction sont exclus des
 *    montées prévues et du fil.
 */
class HomeDashboardQueries
{
    /** Fil « Entrées & sorties » : arrivées (déduites), départs et retours (journal). */
    public const FEED_MOVEMENTS = ['joined', 'left', 'returned'];
    /** Fil « Montées de rang » : montées d'ancienneté (déduites) et changements faits sur le site. */
    public const FEED_PROMOTIONS = ['promoted', 'rank_changed'];

    private const CLASSIC_RANK = 'lead = false AND required_days IS NOT NULL';

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Compteurs actifs / archivés / recrutés par moi, leurs valeurs il y a 30 jours,
     * et le flux entrées / sorties sur 30 jours.
     */
    public function counters(\DateTimeImmutable $now, ?int $myCharacterId): array
    {
        $row = $this->connection->fetchAssociative(<<<'SQL'
            SELECT
              COUNT(*) FILTER (WHERE NOT is_archived)                                                 AS active,
              COUNT(*) FILTER (WHERE recruited_at <= :ago AND (NOT is_archived OR archived_at > :ago)) AS active_ago,
              COUNT(*) FILTER (WHERE is_archived)                                                     AS archived,
              COUNT(*) FILTER (WHERE is_archived AND (archived_at IS NULL OR archived_at <= :ago))    AS archived_ago,
              COUNT(*) FILTER (WHERE is_archived AND archived_at IS NULL)                             AS undated_departures,
              COUNT(*) FILTER (WHERE recruiter_id = :me)                                              AS recruited_by_me,
              COUNT(*) FILTER (WHERE recruiter_id = :me AND recruited_at > :ago AND recruited_at <= :now) AS recruited_by_me_30,
              COUNT(*) FILTER (WHERE recruited_at > :ago AND recruited_at <= :now)                    AS entries_30,
              COUNT(*) FILTER (WHERE archived_at > :ago AND archived_at <= :now)                      AS exits_30
            FROM characters
        SQL, [
            'now' => $this->ts($now),
            'ago' => $this->ts($now->modify('-30 days')),
            'me' => $myCharacterId,
        ]);

        $row = array_map('intval', $row);

        return [
            'active' => ['value' => $row['active'], 'delta30' => $row['active'] - $row['active_ago']],
            'archived' => ['value' => $row['archived'], 'delta30' => $row['archived'] - $row['archived_ago']],
            'recruitedByMe' => ['value' => $row['recruited_by_me'], 'delta30' => $row['recruited_by_me_30']],
            'flow30' => [
                'entries' => $row['entries_30'],
                'exits' => $row['exits_30'],
                'net' => $row['entries_30'] - $row['exits_30'],
            ],
            'undatedDepartures' => $row['undated_departures'],
        ];
    }

    /**
     * Fidélité à J+61 : part des recrues encore présentes 61 jours après leur arrivée.
     *
     * Seules les cohortes matures comptent (recrues arrivées il y a au moins 61 jours), sur
     * les $cohortMonths mois de recrutement précédents. Les départs non datés sont exclus du
     * calcul (on ne sait pas s'ils sont partis avant ou après J+61) et comptés à part.
     * L'évolution compare au même indicateur calculé il y a 30 jours, en points.
     */
    public function retention61(\DateTimeImmutable $now, int $cohortMonths = 6): array
    {
        $current = $this->retentionAt($now, 61, $cohortMonths);
        $previous = $this->retentionAt($now->modify('-30 days'), 61, $cohortMonths);

        return $current + [
            'cohortMonths' => $cohortMonths,
            'deltaPoints' => ($current['rate'] !== null && $previous['rate'] !== null)
                ? $current['rate'] - $previous['rate']
                : null,
        ];
    }

    /** @return array{rate: ?int, retained: int, n: int, excluded: int} */
    private function retentionAt(\DateTimeImmutable $at, int $days, int $cohortMonths): array
    {
        $row = $this->connection->fetchAssociative(<<<'SQL'
            SELECT
              COUNT(*) FILTER (WHERE NOT (is_archived AND archived_at IS NULL)) AS n,
              COUNT(*) FILTER (WHERE NOT is_archived
                                  OR archived_at >= recruited_at + make_interval(days => CAST(:days AS int))) AS retained,
              COUNT(*) FILTER (WHERE is_archived AND archived_at IS NULL) AS excluded
            FROM characters
            WHERE recruited_at <= CAST(:at AS timestamp) - make_interval(days => CAST(:days AS int))
              AND recruited_at >  CAST(:at AS timestamp) - make_interval(days => CAST(:days AS int))
                                                         - make_interval(months => CAST(:months AS int))
        SQL, ['at' => $this->ts($at), 'days' => $days, 'months' => $cohortMonths]);

        $n = (int) $row['n'];
        $retained = (int) $row['retained'];

        return [
            'rate' => $n > 0 ? (int) round(100 * $retained / $n) : null,
            'retained' => $retained,
            'n' => $n,
            'excluded' => (int) $row['excluded'],
        ];
    }

    /**
     * Position du personnage parmi les membres actifs, du plus ancien au plus récent.
     * Arrivés le même jour = même position (1 + nombre de membres arrivés avant lui).
     * placesGained : places gagnées depuis 30 jours (départs de plus anciens que lui).
     * Null si pas de personnage lié ou si le personnage est archivé.
     */
    public function mySeniorityRank(\DateTimeImmutable $now, ?int $myCharacterId): ?array
    {
        if ($myCharacterId === null) {
            return null;
        }

        $row = $this->connection->fetchAssociative(<<<'SQL'
            WITH me AS (
              SELECT recruited_at::date AS d, is_archived FROM characters WHERE id = :me
            ),
            active_now AS (
              SELECT recruited_at::date AS d FROM characters WHERE NOT is_archived
            ),
            active_ago AS (
              SELECT recruited_at::date AS d FROM characters
              WHERE recruited_at <= CAST(:ago AS timestamp)
                AND (NOT is_archived OR archived_at > CAST(:ago AS timestamp))
            )
            SELECT me.is_archived,
                   me.d <= CAST(:ago AS date)                              AS was_member,
                   (SELECT COUNT(*) FROM active_now)                       AS total,
                   1 + (SELECT COUNT(*) FROM active_now a WHERE a.d < me.d) AS position,
                   1 + (SELECT COUNT(*) FROM active_ago a WHERE a.d < me.d) AS position_ago
            FROM me
        SQL, ['me' => $myCharacterId, 'ago' => $this->ts($now->modify('-30 days'))]);

        if (!$row || $row['is_archived']) {
            return null;
        }

        $total = (int) $row['total'];
        $position = (int) $row['position'];

        return [
            'position' => $position,
            'total' => $total,
            'topPercent' => $total > 0 ? (int) ceil(100 * $position / $total) : null,
            'placesGained' => $row['was_member'] ? (int) $row['position_ago'] - $position : null,
        ];
    }

    /** Entrées / sorties / solde par mois calendaire, sur les 12 derniers mois (mois courant inclus). */
    public function monthlyFlow(\DateTimeImmutable $now): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            WITH months AS (
              SELECT generate_series(
                date_trunc('month', CAST(:now AS timestamp)) - INTERVAL '11 months',
                date_trunc('month', CAST(:now AS timestamp)),
                INTERVAL '1 month'
              ) AS m
            )
            SELECT to_char(months.m, 'YYYY-MM') AS month,
                   COUNT(DISTINCT e.id) AS entries,
                   COUNT(DISTINCT x.id) AS exits
            FROM months
            LEFT JOIN characters e ON e.recruited_at >= months.m AND e.recruited_at < months.m + INTERVAL '1 month'
                                  AND e.recruited_at <= CAST(:now AS timestamp)
            LEFT JOIN characters x ON x.archived_at >= months.m AND x.archived_at < months.m + INTERVAL '1 month'
                                  AND x.archived_at <= CAST(:now AS timestamp)
            GROUP BY months.m
            ORDER BY months.m
        SQL, ['now' => $this->ts($now)]);

        return array_map(fn (array $r) => [
            'month' => $r['month'],
            'entries' => (int) $r['entries'],
            'exits' => (int) $r['exits'],
            'net' => (int) $r['entries'] - (int) $r['exits'],
        ], $rows);
    }

    /** Répartition des classes des membres actifs (personnages principaux). */
    public function classDistribution(): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT class, COUNT(*) AS count
            FROM characters
            WHERE NOT is_archived
            GROUP BY class
            ORDER BY count DESC, class
        SQL);

        return array_map(fn (array $r) => ['class' => $r['class'], 'count' => (int) $r['count']], $rows);
    }

    /** Montées d'ancienneté qui tombent entre aujourd'hui et aujourd'hui + $days (inclus). */
    public function upcomingPromotions(\DateTimeImmutable $now, int $days = 7): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT c.id, c.pseudo, c.class, r.name AS rank, r.required_days,
                   (c.recruited_at::date + r.required_days) AS reached_on
            FROM characters c
            JOIN ranks cur ON cur.id = c.rank_id AND cur.lead = false AND cur.required_days IS NOT NULL
            JOIN ranks r ON r.lead = false AND r.required_days IS NOT NULL
                        AND r.required_days > (SELECT MIN(required_days) FROM ranks WHERE lead = false AND required_days IS NOT NULL)
            WHERE NOT c.is_archived
              AND (c.recruited_at::date + r.required_days) BETWEEN CAST(:today AS date) AND CAST(:today AS date) + CAST(:days AS int)
            ORDER BY reached_on, c.pseudo
        SQL, [
            'today' => $now->format('Y-m-d'),
            'days' => $days,
        ]);

        return array_map(fn (array $r) => [
            'characterId' => (int) $r['id'],
            'pseudo' => $r['pseudo'],
            'class' => $r['class'],
            'rank' => $r['rank'],
            'requiredDays' => (int) $r['required_days'],
            'date' => $r['reached_on'],
        ], $rows);
    }

    /**
     * Anniversaires d'ancienneté (1 an, 2 ans…) du mois courant, passés compris. Un membre
     * arrivé un 29 février fête son anniversaire le 28 les années non bissextiles.
     */
    public function anniversaries(\DateTimeImmutable $now): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            WITH p AS (SELECT CAST(:today AS date) AS today)
            SELECT c.id, c.pseudo, c.class, c.recruited_at::date AS recruited_on,
                   (EXTRACT(YEAR FROM p.today) - EXTRACT(YEAR FROM c.recruited_at))::int AS years,
                   make_date(
                     EXTRACT(YEAR FROM p.today)::int,
                     EXTRACT(MONTH FROM c.recruited_at)::int,
                     LEAST(
                       EXTRACT(DAY FROM c.recruited_at)::int,
                       EXTRACT(DAY FROM date_trunc('month', p.today) + INTERVAL '1 month - 1 day')::int
                     )
                   ) AS anniversary_on
            FROM characters c, p
            WHERE NOT c.is_archived
              AND EXTRACT(MONTH FROM c.recruited_at) = EXTRACT(MONTH FROM p.today)
              AND EXTRACT(YEAR FROM c.recruited_at) < EXTRACT(YEAR FROM p.today)
            ORDER BY anniversary_on, c.pseudo
        SQL, ['today' => $now->format('Y-m-d')]);

        $today = $now->format('Y-m-d');

        return array_map(fn (array $r) => [
            'characterId' => (int) $r['id'],
            'pseudo' => $r['pseudo'],
            'class' => $r['class'],
            'years' => (int) $r['years'],
            'date' => $r['anniversary_on'],
            'isPast' => $r['anniversary_on'] < $today,
        ], $rows);
    }

    /** Progression du personnage vers son prochain rang d'ancienneté ; null si pas de personnage lié. */
    public function myProgress(\DateTimeImmutable $now, ?int $myCharacterId): ?array
    {
        if ($myCharacterId === null) {
            return null;
        }

        $row = $this->connection->fetchAssociative(sprintf(<<<'SQL'
            SELECT c.id, c.pseudo, c.class, c.recruited_at::date AS recruited_on,
                   st.name AS stored_rank, (st.lead OR st.required_days IS NULL) AS stored_rank_special,
                   d.days,
                   cur.name AS current_rank, cur.required_days AS current_days,
                   nxt.name AS next_rank, nxt.required_days AS next_days
            FROM characters c
            JOIN ranks st ON st.id = c.rank_id
            CROSS JOIN LATERAL (SELECT (CAST(:today AS date) - c.recruited_at::date) AS days) d
            LEFT JOIN LATERAL (
              SELECT name, required_days FROM ranks
              WHERE %1$s AND required_days <= GREATEST(d.days, 1)
              ORDER BY required_days DESC LIMIT 1
            ) cur ON true
            LEFT JOIN LATERAL (
              SELECT name, required_days FROM ranks
              WHERE %1$s AND required_days > GREATEST(d.days, 1)
              ORDER BY required_days LIMIT 1
            ) nxt ON true
            WHERE c.id = :me
        SQL, self::CLASSIC_RANK), [
            'today' => $now->format('Y-m-d'),
            'me' => $myCharacterId,
        ]);

        if (!$row) {
            return null;
        }

        $days = (int) $row['days'];
        $currentDays = $row['current_days'] !== null ? (int) $row['current_days'] : null;
        $nextDays = $row['next_days'] !== null ? (int) $row['next_days'] : null;

        return [
            'characterId' => (int) $row['id'],
            'pseudo' => $row['pseudo'],
            'class' => $row['class'],
            'recruitedAt' => $row['recruited_on'],
            'seniorityDays' => $days,
            'storedRank' => $row['stored_rank'],
            'storedRankIsSpecial' => (bool) $row['stored_rank_special'],
            'seniorityRank' => $row['current_rank'] ? ['name' => $row['current_rank'], 'requiredDays' => $currentDays] : null,
            'nextRank' => $row['next_rank'] ? [
                'name' => $row['next_rank'],
                'requiredDays' => $nextDays,
                'daysLeft' => $nextDays - $days,
                'date' => (new \DateTimeImmutable($row['recruited_on']))->modify("+$nextDays days")->format('Y-m-d'),
            ] : null,
            'progressPercent' => ($nextDays !== null && $currentDays !== null && $nextDays > $currentDays)
                ? (int) floor(100 * max(0, $days - $currentDays) / ($nextDays - $currentDays))
                : 100,
        ];
    }

    /**
     * Fil d'activité, du plus récent au plus ancien, limité aux types demandés (voir
     * FEED_MOVEMENTS et FEED_PROMOTIONS) : arrivées (recruited_at), montées d'ancienneté
     * (calculées) et événements du journal.
     *
     * @param list<string> $types
     */
    public function activity(\DateTimeImmutable $now, array $types, int $limit = 20, int $offset = 0): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT * FROM (
              SELECT 'joined' AS type, c.recruited_at AS occurred_at, c.id AS character_id,
                     c.pseudo AS character_pseudo, c.class, NULL AS mule_pseudo,
                     jsonb_build_object('recruiter', rec.pseudo) AS details
              FROM characters c
              LEFT JOIN characters rec ON rec.id = c.recruiter_id

              UNION ALL

              SELECT 'promoted', CAST(c.recruited_at::date + r.required_days AS timestamp), c.id,
                     c.pseudo, c.class, NULL,
                     jsonb_build_object('rank', r.name, 'requiredDays', r.required_days)
              FROM characters c
              JOIN ranks cur ON cur.id = c.rank_id AND cur.lead = false AND cur.required_days IS NOT NULL
              JOIN ranks r ON r.lead = false AND r.required_days IS NOT NULL
                        AND r.required_days > (SELECT MIN(required_days) FROM ranks WHERE lead = false AND required_days IS NOT NULL)
              -- encore membre à la date de la montée
              WHERE NOT (c.is_archived AND (c.archived_at IS NULL OR c.archived_at < c.recruited_at::date + r.required_days))

              UNION ALL

              SELECT e.type, e.occurred_at, e.character_id, e.character_pseudo, c.class, e.mule_pseudo, e.payload
              FROM guild_event e
              LEFT JOIN characters c ON c.id = e.character_id
            ) feed
            WHERE occurred_at <= CAST(:now AS timestamp)
              AND type IN (:types)
            ORDER BY occurred_at DESC, character_pseudo
            LIMIT CAST(:limit AS int) OFFSET CAST(:offset AS int)
        SQL, [
            'now' => $this->ts($now),
            'types' => $types,
            'limit' => $limit,
            'offset' => $offset,
        ], [
            'types' => ArrayParameterType::STRING,
        ]);

        return array_map(fn (array $r) => [
            'type' => $r['type'],
            'occurredAt' => $r['occurred_at'],
            'characterId' => $r['character_id'] !== null ? (int) $r['character_id'] : null,
            'pseudo' => $r['character_pseudo'],
            'class' => $r['class'],
            'mulePseudo' => $r['mule_pseudo'],
            'details' => json_decode($r['details'] ?? '{}', true) ?: [],
        ], $rows);
    }

    /**
     * Manche fantômes ouverte et ma participation ; null s'il n'y en a pas.
     *
     * Comme la page Fantômes, les membres déjà archivés (exclus en cours de manche) ne
     * comptent plus dans les votants, les votes ni « au-dessus du seuil ». Ma participation
     * (myVotes / hasVoted) compte en revanche tous mes votes de la manche.
     */
    public function ghostRoundStatus(int $userId): ?array
    {
        $row = $this->connection->fetchAssociative(<<<'SQL'
            SELECT r.id, r.opened_at::date AS opened_on, r.threshold,
                   (SELECT COUNT(*) FROM ghost_vote mv
                     WHERE mv.round_id = r.id AND mv.voter_id = :user)             AS my_votes,
                   COUNT(v.id)                                                     AS total_votes,
                   COUNT(DISTINCT v.voter_id)                                      AS voters,
                   (SELECT COUNT(*) FROM (
                      SELECT v2.character_id FROM ghost_vote v2
                      JOIN characters c2 ON c2.id = v2.character_id AND NOT c2.is_archived
                      WHERE v2.round_id = r.id
                      GROUP BY v2.character_id
                      HAVING COUNT(*) >= r.threshold
                   ) flagged)                                                      AS flagged
            FROM ghost_round r
            LEFT JOIN (
              ghost_vote v JOIN characters c ON c.id = v.character_id AND NOT c.is_archived
            ) ON v.round_id = r.id
            WHERE r.closed_at IS NULL
            GROUP BY r.id
            ORDER BY r.opened_at DESC
            LIMIT 1
        SQL, ['user' => $userId]);

        if (!$row) {
            return null;
        }

        return [
            'roundId' => (int) $row['id'],
            'openedAt' => $row['opened_on'],
            'threshold' => (int) $row['threshold'],
            'myVotes' => (int) $row['my_votes'],
            'hasVoted' => (int) $row['my_votes'] > 0,
            'totalVotes' => (int) $row['total_votes'],
            'voters' => (int) $row['voters'],
            'flagged' => (int) $row['flagged'],
        ];
    }

    /** Recrues actives arrivées il y a moins de $days jours, avec leurs signaux d'alerte. */
    public function freshRecruits(\DateTimeImmutable $now, int $days = 15): array
    {
        $rows = $this->connection->fetchAllAssociative(<<<'SQL'
            SELECT c.id, c.pseudo, c.class, c.recruited_at::date AS recruited_on,
                   (CAST(:today AS date) - c.recruited_at::date) AS days,
                   rec.pseudo AS recruiter,
                   (SELECT COUNT(*) FROM warnings w WHERE w.character_id = c.id) AS warnings,
                   (SELECT COUNT(*) FROM ghost_vote v JOIN ghost_round gr ON gr.id = v.round_id
                     WHERE v.character_id = c.id AND gr.closed_at IS NULL) AS ghost_votes
            FROM characters c
            LEFT JOIN characters rec ON rec.id = c.recruiter_id
            WHERE NOT c.is_archived
              AND c.recruited_at::date > CAST(:today AS date) - CAST(:days AS int)
              AND c.recruited_at <= CAST(:now AS timestamp)
            ORDER BY c.recruited_at DESC, c.pseudo
        SQL, [
            'today' => $now->format('Y-m-d'),
            'now' => $this->ts($now),
            'days' => $days,
        ]);

        return array_map(fn (array $r) => [
            'characterId' => (int) $r['id'],
            'pseudo' => $r['pseudo'],
            'class' => $r['class'],
            'recruitedAt' => $r['recruited_on'],
            'days' => (int) $r['days'],
            'recruiter' => $r['recruiter'],
            'warnings' => (int) $r['warnings'],
            'ghostVotes' => (int) $r['ghost_votes'],
        ], $rows);
    }

    private function ts(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
