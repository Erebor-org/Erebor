<?php

namespace App\Service\Gw2;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Server-side client for https://api.guildwars2.com/v2. The API key (GW2_API_KEY) only lives
 * here: it is sent as a Bearer header and never returned to the browser.
 *
 * Caching: account-bound data and trading post prices ~5 min, static data (items,
 * specializations, guilds) for a day. Errors are never cached.
 */
class Gw2ApiClient
{
    public const BASE_URL = 'https://api.guildwars2.com/v2';
    // Pin the response schema so a GW2 API update can't silently change payload shapes
    public const SCHEMA_VERSION = '2022-03-23T19:00:00.000Z';
    public const MAX_IDS_PER_REQUEST = 200;
    // Names and descriptions of static data (items, stats, specializations) in the site's language
    public const LANG = 'fr';

    public const TTL_ACCOUNT = 300;
    public const TTL_PRICES = 300;
    public const TTL_STATIC = 86400;

    public const EXPECTED_PERMISSIONS = [
        'account', 'inventories', 'characters', 'wallet', 'tradingpost', 'unlocks', 'progression',
    ];

    private LoggerInterface $logger;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $gw2Cache,
        private string $apiKey,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function getTokenInfo(): array
    {
        return $this->cachedAccount('tokeninfo', fn () => $this->get('/tokeninfo'));
    }

    public function getAccount(): array
    {
        return $this->cachedAccount('account', fn () => $this->get('/account'));
    }

    /** @return string[] character names */
    public function getCharacterNames(): array
    {
        return $this->cachedAccount('characters', fn () => $this->get('/characters'));
    }

    /**
     * /characters/{name}/core for every name, requested concurrently.
     *
     * @return array<string, array> core data keyed by character name
     */
    public function getCharacterCores(array $names): array
    {
        return $this->getPerCharacter($names, 'core');
    }

    /**
     * /characters/{name}/inventory (the bags) for every name, requested concurrently.
     *
     * @return array<string, array> inventory keyed by character name
     */
    public function getCharacterInventories(array $names): array
    {
        return $this->getPerCharacter($names, 'inventory');
    }

    /** Bank slots, in order; empty slots are null */
    public function getBank(): array
    {
        return $this->cachedAccount('bank', fn () => $this->get('/account/bank'));
    }

    /** Shared inventory slots (available to every character); empty slots are null */
    public function getSharedInventory(): array
    {
        return $this->cachedAccount('shared_inventory', fn () => $this->get('/account/inventory'));
    }

    /**
     * Drops the cached bags, shared slots and bank so the next read hits the API again
     * (static data like item names stays cached).
     */
    public function forgetInventories(array $names): void
    {
        $keys = [$this->accountKey('characters'), $this->accountKey('shared_inventory'), $this->accountKey('bank')];
        foreach ($names as $name) {
            $keys[] = $this->accountKey('inventory.'.$name);
        }
        $this->gw2Cache->deleteItems($keys);
    }

    /** Drops the cached trading post prices of these items */
    public function forgetPrices(array $ids): void
    {
        $this->gw2Cache->deleteItems(array_map(fn ($id) => $this->staticKey('prices.'.(int) $id), $ids));
    }

    /** @return int[] ids of every item the material storage accepts */
    public function getStorableMaterialIds(): array
    {
        return $this->remember($this->staticKey('materials'), self::TTL_STATIC, function () {
            $ids = [];
            foreach ($this->get('/materials', ['ids' => 'all']) as $category) {
                $ids = array_merge($ids, $category['items'] ?? []);
            }

            return $ids;
        });
    }

    /** Full character payload (bags, equipment, build tabs, crafting...) */
    public function getCharacter(string $name): array
    {
        return $this->cachedAccount('character.'.$name, fn () => $this->get('/characters/'.rawurlencode($name)));
    }

    /** @return array<int, array> items keyed by id; unknown ids are left out */
    public function getItems(array $ids): array
    {
        return $this->getByIds('/items', $ids, self::TTL_STATIC, 'items', true);
    }

    /** @return array<int, array> stat combinations ("Berserker", "Rampager"...) keyed by id */
    public function getItemStats(array $ids): array
    {
        return $this->getByIds('/itemstats', $ids, self::TTL_STATIC, 'itemstats', true);
    }

    /** @return array<int, array> trading post prices keyed by item id; untradable ids are left out */
    public function getPrices(array $ids): array
    {
        return $this->getByIds('/commerce/prices', $ids, self::TTL_PRICES, 'prices');
    }

    /** @return array<int, array> specializations keyed by id */
    public function getSpecializations(array $ids): array
    {
        return $this->getByIds('/specializations', $ids, self::TTL_STATIC, 'specializations', true);
    }

    public function getGuild(string $guildId): array
    {
        return $this->remember($this->staticKey('guild.'.$guildId), self::TTL_STATIC, fn () => $this->get('/guild/'.rawurlencode($guildId)));
    }

    /**
     * Bulk endpoints accept ?ids= with at most 200 ids. Each id is cached on its own, so
     * only the missing ones are fetched, in batches.
     */
    private function getByIds(string $endpoint, array $ids, int $ttl, string $namespace, bool $localized = false): array
    {
        $query = $localized ? ['lang' => self::LANG] : [];
        if ($localized) {
            $namespace .= '.'.self::LANG;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id) => $id > 0)));

        $result = [];
        $missing = [];
        foreach ($ids as $id) {
            $item = $this->gw2Cache->getItem($this->staticKey($namespace.'.'.$id));
            if ($item->isHit()) {
                if (null !== $item->get()) {
                    $result[$id] = $item->get();
                }
            } else {
                $missing[$id] = $item;
            }
        }

        foreach (array_chunk(array_keys($missing), self::MAX_IDS_PER_REQUEST) as $chunk) {
            try {
                $entries = $this->get($endpoint, $query + ['ids' => implode(',', $chunk)]);
            } catch (Gw2ApiException $e) {
                // The API answers 404 "all ids provided are invalid" when no id of the batch exists
                if (Gw2ApiException::NOT_FOUND !== $e->getReason()) {
                    throw $e;
                }
                $entries = [];
            }

            $found = array_column($entries, null, 'id');
            foreach ($chunk as $id) {
                // Unknown ids are cached as null too, so they aren't requested again and again
                $missing[$id]->set($found[$id] ?? null)->expiresAfter($ttl);
                $this->gw2Cache->save($missing[$id]);
                if (isset($found[$id])) {
                    $result[$id] = $found[$id];
                }
            }
        }

        return $result;
    }

    /** One request per character, all sent before any is read so they run concurrently */
    private function getPerCharacter(array $names, string $endpoint): array
    {
        $result = [];
        $pending = [];
        foreach ($names as $name) {
            $item = $this->gw2Cache->getItem($this->accountKey($endpoint.'.'.$name));
            if ($item->isHit()) {
                $result[$name] = $item->get();
            } else {
                $pending[$name] = [$item, $this->request('/characters/'.rawurlencode($name).'/'.$endpoint)];
            }
        }
        foreach ($pending as $name => [$item, $response]) {
            $result[$name] = $this->decode($response);
            $item->set($result[$name])->expiresAfter(self::TTL_ACCOUNT);
            $this->gw2Cache->save($item);
        }

        return $result;
    }

    private function cachedAccount(string $key, callable $fetch): array
    {
        return $this->remember($this->accountKey($key), self::TTL_ACCOUNT, $fetch);
    }

    // A failing $fetch throws before save(), so errors are never cached
    private function remember(string $key, int $ttl, callable $fetch): array
    {
        $item = $this->gw2Cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $value = $fetch();
        $item->set($value)->expiresAfter($ttl);
        $this->gw2Cache->save($item);

        return $value;
    }

    // Account data is scoped by a hash of the key, so changing the key never serves the old account
    private function accountKey(string $key): string
    {
        return 'account.'.substr(hash('sha256', $this->apiKey), 0, 16).'.'.$this->sanitizeKey($key);
    }

    private function staticKey(string $key): string
    {
        return 'static.'.$this->sanitizeKey($key);
    }

    // PSR-6 forbids {}()/\@: in keys, character names may contain spaces and accents
    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^A-Za-z0-9_.]/', '_', $key).'.'.substr(md5($key), 0, 8);
    }

    private function get(string $path, array $query = []): array
    {
        return $this->decode($this->request($path, $query));
    }

    private function request(string $path, array $query = []): ResponseInterface
    {
        if ('' === trim($this->apiKey)) {
            throw new Gw2ApiException(Gw2ApiException::NOT_CONFIGURED, 'GW2_API_KEY is not configured.');
        }

        try {
            return $this->httpClient->request('GET', self::BASE_URL.$path, [
                'query' => $query,
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'X-Schema-Version' => self::SCHEMA_VERSION,
                    'Accept' => 'application/json',
                ],
                'timeout' => 10,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw $this->unavailable($path, $e);
        }
    }

    private function decode(ResponseInterface $response): array
    {
        $path = parse_url($response->getInfo('url') ?? '', PHP_URL_PATH) ?: '?';

        try {
            $status = $response->getStatusCode();
            $body = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw $this->unavailable($path, $e);
        }

        $data = json_decode($body, true);
        $text = is_array($data) && isset($data['text']) && is_string($data['text']) ? $data['text'] : '';

        // 206 = bulk request where only some ids exist: the body still lists the valid ones
        if ($status >= 200 && $status < 300) {
            if (!is_array($data)) {
                throw $this->unavailable($path, null, 'Malformed JSON from the GW2 API.');
            }

            return $data;
        }

        if (429 === $status) {
            throw new Gw2ApiException(Gw2ApiException::RATE_LIMITED, 'GW2 API rate limit reached, retry in a minute.');
        }
        if (in_array($status, [401, 403], true)) {
            if (preg_match('/scope|permission/i', $text)) {
                throw new Gw2ApiException(Gw2ApiException::MISSING_PERMISSION, 'The GW2 API key is missing a permission: '.$text);
            }

            throw new Gw2ApiException(Gw2ApiException::INVALID_KEY, 'The GW2 API key is invalid or revoked.');
        }
        if (404 === $status) {
            throw new Gw2ApiException(Gw2ApiException::NOT_FOUND, '' !== $text ? $text : 'Not found.');
        }

        throw $this->unavailable($path, null, sprintf('GW2 API answered HTTP %d%s', $status, '' !== $text ? ': '.$text : ''));
    }

    private function unavailable(string $path, ?\Throwable $previous, string $detail = 'GW2 API unreachable.'): Gw2ApiException
    {
        $this->logger->warning('GW2 API error on {path}: {detail}', [
            'path' => $path,
            'detail' => $previous ? $previous->getMessage() : $detail,
        ]);

        return new Gw2ApiException(Gw2ApiException::UNAVAILABLE, $detail, $previous);
    }
}
