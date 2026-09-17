<?php

namespace App\Service;

/**
 * Parses a raw Dofus guild chat log and extracts the pseudos of members
 * who left the guild, ignoring any other kind of log line (connection
 * messages, etc.) that happens to mention a pseudo.
 */
class GuildLogParserService
{
    // Optional "[HH:MM]" timestamp, then the pseudo, then the fixed departure phrase.
    // Deliberately not anchored to line start/end: some sources paste the whole log as a
    // single line (or otherwise lose line breaks), which would otherwise hide every entry
    // past the first. The pseudo capture excludes "[" so it can never swallow the next
    // entry's timestamp bracket when two departures are packed onto one line — without
    // that, the [HH:MM] of the second entry would end up glued to its pseudo instead of
    // being consumed by the optional timestamp group.
    private const DEPARTURE_PATTERN = '/(?:\[\d{1,2}:\d{2}\]\s*)?([^\[\]\n]+?)\s+ne fait plus partie de la guilde\.?/u';

    /**
     * @return array{detectedCount: int, uniquePseudos: string[]}
     */
    public function parse(string $log): array
    {
        preg_match_all(self::DEPARTURE_PATTERN, $log, $matches);

        $raw = array_values(array_filter(array_map('trim', $matches[1]), fn (string $p) => $p !== ''));

        $seen = [];
        $uniquePseudos = [];
        foreach ($raw as $pseudo) {
            $key = mb_strtolower($pseudo);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniquePseudos[] = $pseudo;
            }
        }

        return [
            'detectedCount' => count($raw),
            'uniquePseudos' => $uniquePseudos,
        ];
    }
}
