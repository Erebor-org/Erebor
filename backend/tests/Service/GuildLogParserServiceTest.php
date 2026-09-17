<?php

namespace App\Tests\Service;

use App\Service\GuildLogParserService;
use PHPUnit\Framework\TestCase;

class GuildLogParserServiceTest extends TestCase
{
    private GuildLogParserService $parser;

    protected function setUp(): void
    {
        $this->parser = new GuildLogParserService();
    }

    public function testExtractsPseudoFromSingleDepartureLine(): void
    {
        $result = $this->parser->parse('[20:39] Red-Huissier ne fait plus partie de la guilde.');

        $this->assertSame(1, $result['detectedCount']);
        $this->assertSame(['Red-Huissier'], $result['uniquePseudos']);
    }

    public function testExtractsAllPseudosFromMultipleLines(): void
    {
        $log = <<<LOG
        [20:39] Red-Huissier ne fait plus partie de la guilde.
        [20:39] Worldeater ne fait plus partie de la guilde.
        [20:39] Hoclope ne fait plus partie de la guilde.
        [20:40] Bambou-Kid ne fait plus partie de la guilde.
        LOG;

        $result = $this->parser->parse($log);

        $this->assertSame(4, $result['detectedCount']);
        $this->assertSame(['Red-Huissier', 'Worldeater', 'Hoclope', 'Bambou-Kid'], $result['uniquePseudos']);
    }

    public function testIgnoresUnrelatedGuildMessages(): void
    {
        $log = <<<LOG
        Un membre de votre guilde, Toto, est en ligne.
        [20:39] Red-Huissier ne fait plus partie de la guilde.
        LOG;

        $result = $this->parser->parse($log);

        $this->assertSame(['Red-Huissier'], $result['uniquePseudos']);
        $this->assertNotContains('Toto', $result['uniquePseudos']);
    }

    public function testDeduplicatesRepeatedPseudosCaseInsensitively(): void
    {
        $log = <<<LOG
        [20:40] Lipsticks ne fait plus partie de la guilde.
        [20:41] Lipsticks ne fait plus partie de la guilde.
        [20:42] lipsticks ne fait plus partie de la guilde.
        LOG;

        $result = $this->parser->parse($log);

        $this->assertSame(3, $result['detectedCount']);
        $this->assertSame(['Lipsticks'], $result['uniquePseudos']);
    }

    public function testHandlesExtraWhitespaceAndMissingTimestamp(): void
    {
        $result = $this->parser->parse('  Some-Pseudo   ne fait plus partie de la guilde.  ');

        $this->assertSame(['Some-Pseudo'], $result['uniquePseudos']);
    }

    public function testReturnsNoPseudosForEmptyOrNoiseOnlyLog(): void
    {
        $result = $this->parser->parse("Un membre de votre guilde, X, est en ligne.\nBienvenue sur le serveur !");

        $this->assertSame(0, $result['detectedCount']);
        $this->assertSame([], $result['uniquePseudos']);
    }

    public function testDoesNotChokeOnSqlLikeOrSpecialCharactersInPseudo(): void
    {
        $result = $this->parser->parse("[12:00] Robert'); DROP TABLE characters;-- ne fait plus partie de la guilde.");

        $this->assertSame(["Robert'); DROP TABLE characters;--"], $result['uniquePseudos']);
    }

    public function testExtractsSeveralDeparturesPastedAsASingleLineWithoutLineBreaks(): void
    {
        // Some sources (a chat client, a browser copy) collapse line breaks into spaces —
        // the parser must not require one departure message per physical line.
        $log = '[20:39] Red-Huissier ne fait plus partie de la guilde. '
            . '[20:40] Worldeater ne fait plus partie de la guilde. '
            . '[20:41] Hoclope ne fait plus partie de la guilde.';

        $result = $this->parser->parse($log);

        $this->assertSame(3, $result['detectedCount']);
        $this->assertSame(['Red-Huissier', 'Worldeater', 'Hoclope'], $result['uniquePseudos']);
    }

    public function testExtractsSeveralDeparturesOnOneLineEvenWithoutTimestampsOrPunctuation(): void
    {
        $log = 'Red-Huissier ne fait plus partie de la guilde Worldeater ne fait plus partie de la guilde';

        $result = $this->parser->parse($log);

        $this->assertSame(['Red-Huissier', 'Worldeater'], $result['uniquePseudos']);
    }

    public function testStillHandlesMixedLineBreaksAndConcatenatedEntries(): void
    {
        $log = "[20:39] Red-Huissier ne fait plus partie de la guilde. [20:40] Worldeater ne fait plus partie de la guilde.\n"
            . '[20:41] Hoclope ne fait plus partie de la guilde.';

        $result = $this->parser->parse($log);

        $this->assertSame(['Red-Huissier', 'Worldeater', 'Hoclope'], $result['uniquePseudos']);
    }
}
