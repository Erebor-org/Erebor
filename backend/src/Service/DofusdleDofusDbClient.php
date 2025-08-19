<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DofusdleDofusDbClient
{
    private string $baseUrl;
    private HttpClientInterface $client;
    private string $referer;

    public function __construct(HttpClientInterface $client)
    {
        $this->baseUrl = 'https://api.dofusdb.fr';
        $this->client = $client;
        $this->referer = 'Erebor';
    }

    public function request(string $endpoint, array $query = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $options = [
            'headers' => [
                'Referer' => $this->referer,
                'Accept' => 'application/json',
            ],
            'query' => $query,
        ];
        $response = $this->client->request('GET', $url, $options);
        return $response->toArray();
    }

    private function extractFrFields(array $results, array $fields = ['name', 'description']): array
    {
        return array_map(function ($item) use ($fields) {
            foreach ($fields as $field) {
                if (isset($item[$field]) && is_array($item[$field]) && isset($item[$field]['fr'])) {
                    $item[$field] = $item[$field]['fr'];
                }
            }
            return $item;
        }, $results);
    }

    public function findMonsters(array $query = []): array
    {
        // Always fetch a large limit unless overridden
        if (!isset($query['$limit'])) {
            $query['$limit'] = 5000;
        }
        $results = $this->request('/monsters', $query);
        // The API response is { total, limit, skip, data: [...] }
        $data = $results['data'] ?? [];
        return $this->extractFrFields($data, ['name', 'description', 'family', 'subareaNames']);
    }

    public function findSpells(array $query = []): array
    {
        $results = $this->request('/spells', $query);
        return $this->extractFrFields($results, ['name', 'description']);
    }

    public function findSpellLevels(array $query = []): array
    {
        $results = $this->request('/spell-levels', $query);
        return $this->extractFrFields($results, ['name', 'description']);
    }

    public function findDungeons(array $query = []): array
    {
        $results = $this->request('/dungeons', $query);
        return $this->extractFrFields($results, ['name', 'description']);
    }

    public function findItems(array $query = []): array
    {
        $results = $this->request('/items', $query);
        return $this->extractFrFields($results, ['name', 'description']);
    }

    public function findRecipes(array $query = []): array
    {
        return $this->request('/recipes', $query);
    }

    public function getMonsterRaceName(int $raceId): ?string
    {
        if (!$raceId) return 'Inconnue';
        try {
            $result = $this->request('/monster-races/' . $raceId);
            return $result['name']['fr'] ?? 'Inconnue';
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            // 404 not found, race inconnue
            return 'Inconnue';
        }
    }

    public function getMonsterSuperRaceName(?int $superRaceId): ?string
    {
        if (!$superRaceId) return null;
        try {
            $result = $this->request('/monster-super-races/' . $superRaceId);
            return $result['name']['fr'] ?? null;
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            return null;
        }
    }

    public function getSubareaNamesAndAreas(array $subareaIds): array
    {
        $subareas = [];
        $areas = [];
        foreach ($subareaIds as $id) {
            try {
                $sub = $this->request('/subareas/' . $id);
                $subareas[] = $sub['name']['fr'] ?? null;
                if (isset($sub['areaId'])) {
                    try {
                        $area = $this->request('/areas/' . $sub['areaId']);
                        $areas[] = $area['name']['fr'] ?? null;
                    } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {}
                }
            } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {}
        }
        return ['subareas' => array_filter($subareas), 'areas' => array_filter($areas)];
    }

    public function getMiniBossName(?int $miniBossId): ?string
    {
        if (!$miniBossId) return null;
        try {
            $result = $this->request('/monsters/' . $miniBossId);
            return $result['name']['fr'] ?? null;
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            return null;
        }
    }
}
