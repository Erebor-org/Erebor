<?php

namespace App\Tests\Service;

use App\Service\Gw2\Gw2ApiClient;
use App\Service\Gw2\Gw2ApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Every GW2 API answer is simulated with MockHttpClient: nothing here reaches api.guildwars2.com.
 */
class Gw2ApiClientTest extends TestCase
{
    private const KEY = 'TEST-KEY-0000';

    /** @var array<int, array{method: string, url: string, options: array}> */
    private array $requests = [];

    /**
     * @param callable(string $url): MockResponse $responder
     */
    private function makeClient(callable $responder, ?ArrayAdapter $cache = null, string $key = self::KEY): Gw2ApiClient
    {
        $http = new MockHttpClient(function (string $method, string $url, array $options) use ($responder) {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            return $responder($url);
        });

        return new Gw2ApiClient($http, $cache ?? new ArrayAdapter(), $key);
    }

    private static function json(mixed $data, int $status = 200): MockResponse
    {
        return new MockResponse(json_encode($data), ['http_code' => $status]);
    }

    /** @return int[] ids sent in the ?ids= parameter of a request */
    private static function idsOf(string $url): array
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return array_map('intval', explode(',', $query['ids']));
    }

    public function testSendsTheKeyAsBearerHeaderAndNeverInTheUrl(): void
    {
        $client = $this->makeClient(fn () => self::json(['name' => 'Website perso', 'permissions' => ['account']]));

        $this->assertSame('Website perso', $client->getTokenInfo()['name']);
        $this->assertCount(1, $this->requests);

        $request = $this->requests[0];
        $this->assertSame('https://api.guildwars2.com/v2/tokeninfo', $request['url']);
        $this->assertContains('Authorization: Bearer '.self::KEY, $request['options']['headers']);
        $this->assertStringNotContainsString(self::KEY, $request['url']);
    }

    public function testAccountDataIsCached(): void
    {
        $client = $this->makeClient(fn () => self::json(['Siisko Siam']));

        $this->assertSame(['Siisko Siam'], $client->getCharacterNames());
        $this->assertSame(['Siisko Siam'], $client->getCharacterNames());
        $this->assertCount(1, $this->requests);
    }

    public function testAccountCacheIsScopedToTheKey(): void
    {
        $cache = new ArrayAdapter();
        $this->makeClient(fn () => self::json(['Old Char']), $cache, 'KEY-A')->getCharacterNames();
        $names = $this->makeClient(fn () => self::json(['New Char']), $cache, 'KEY-B')->getCharacterNames();

        $this->assertSame(['New Char'], $names);
        $this->assertCount(2, $this->requests);
    }

    public function testItemsAreRequestedInBatchesOf200AndCachedPerId(): void
    {
        $client = $this->makeClient(function (string $url) {
            return self::json(array_map(fn (int $id) => ['id' => $id, 'name' => 'Item '.$id], self::idsOf($url)));
        });

        $ids = range(1, 450);
        $items = $client->getItems(array_merge($ids, [1, 2, 3])); // duplicates are dropped

        $this->assertCount(450, $items);
        $this->assertSame('Item 42', $items[42]['name']);
        $this->assertCount(3, $this->requests); // 200 + 200 + 50
        $this->assertSame([200, 200, 50], array_map(fn ($r) => count(self::idsOf($r['url'])), $this->requests));
        $this->assertStringStartsWith('https://api.guildwars2.com/v2/items?', $this->requests[0]['url']);
        $this->assertStringContainsString('lang=fr', $this->requests[0]['url']); // names in French

        // Second call: everything cached, only the new id is fetched
        $client->getItems([10, 20, 451]);
        $this->assertCount(4, $this->requests);
        $this->assertSame([451], self::idsOf($this->requests[3]['url']));
    }

    public function testPricesHandlePartialAndAllInvalidBatches(): void
    {
        $client = $this->makeClient(function (string $url) {
            $ids = self::idsOf($url);
            if ([1, 2] === $ids) {
                return self::json(['text' => 'all ids provided are invalid'], 404);
            }

            // 206 Partial Content: only the tradable ids are listed
            return self::json([['id' => 19684, 'buys' => ['unit_price' => 59], 'sells' => ['unit_price' => 68]]], 206);
        });

        $prices = $client->getPrices([19684, 3]);
        $this->assertSame([19684], array_keys($prices));
        $this->assertSame(68, $prices[19684]['sells']['unit_price']);

        $this->assertSame([], $client->getPrices([1, 2]));

        // Unknown ids are cached too: asking again doesn't hit the API
        $client->getPrices([19684, 3, 1, 2]);
        $this->assertCount(2, $this->requests);
        $this->assertStringStartsWith('https://api.guildwars2.com/v2/commerce/prices?', $this->requests[0]['url']);
        $this->assertStringNotContainsString('lang=', $this->requests[0]['url']);
    }

    public function testCharacterCoresAreFetchedPerCharacterWithEncodedNames(): void
    {
        $client = $this->makeClient(fn (string $url) => self::json([
            'name' => urldecode(explode('/', parse_url($url, PHP_URL_PATH))[3]),
            'profession' => 'Guardian',
        ]));

        $cores = $client->getCharacterCores(['Siisko Siam', 'Élise']);

        $this->assertSame('Guardian', $cores['Siisko Siam']['profession']);
        $this->assertSame('Élise', $cores['Élise']['name']);
        $this->assertSame('https://api.guildwars2.com/v2/characters/Siisko%20Siam/core', $this->requests[0]['url']);

        $client->getCharacterCores(['Siisko Siam']);
        $this->assertCount(2, $this->requests);
    }

    public function errorProvider(): iterable
    {
        yield 'invalid key' => [self::json(['text' => 'Invalid access token'], 401), Gw2ApiException::INVALID_KEY];
        yield 'missing permission' => [self::json(['text' => 'requires scope inventories'], 403), Gw2ApiException::MISSING_PERMISSION];
        yield 'rate limit' => [self::json(['text' => 'too many requests'], 429), Gw2ApiException::RATE_LIMITED];
        yield 'api down' => [self::json(['text' => 'API not active'], 503), Gw2ApiException::UNAVAILABLE];
        yield 'not found' => [self::json(['text' => 'no such id'], 404), Gw2ApiException::NOT_FOUND];
        yield 'malformed body' => [new MockResponse('<html>oops</html>'), Gw2ApiException::UNAVAILABLE];
        yield 'network failure' => [new MockResponse([new TransportException('Connection refused')]), Gw2ApiException::UNAVAILABLE];
    }

    /**
     * @dataProvider errorProvider
     */
    public function testApiErrorsAreMappedAndNeverCached(MockResponse $response, string $expectedReason): void
    {
        $responses = [$response, self::json(['name' => 'Siisko Siam'])];
        $client = $this->makeClient(function () use (&$responses) {
            return array_shift($responses);
        });

        try {
            $client->getCharacter('Siisko Siam');
            $this->fail('A Gw2ApiException was expected');
        } catch (Gw2ApiException $e) {
            $this->assertSame($expectedReason, $e->getReason());
            $this->assertStringNotContainsString(self::KEY, $e->getMessage());
        }

        // The failure was not cached: the next call reaches the API again and succeeds
        $this->assertSame('Siisko Siam', $client->getCharacter('Siisko Siam')['name']);
        $this->assertCount(2, $this->requests);
    }

    public function testMissingKeyFailsWithoutCallingTheApi(): void
    {
        $client = $this->makeClient(fn () => self::json([]), null, '');

        try {
            $client->getTokenInfo();
            $this->fail('A Gw2ApiException was expected');
        } catch (Gw2ApiException $e) {
            $this->assertSame(Gw2ApiException::NOT_CONFIGURED, $e->getReason());
        }
        $this->assertCount(0, $this->requests);
    }
}
