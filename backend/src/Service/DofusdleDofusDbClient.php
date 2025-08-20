<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DofusdleDofusDbClient
{
    private string $baseUrl;
    private HttpClientInterface $client;
    private string $referer;
    private int $maxRetries;
    private int $retryDelay;

    public function __construct(HttpClientInterface $client)
    {
        $this->baseUrl = 'https://api.dofusdb.fr';
        $this->client = $client;
        $this->referer = 'Erebor';
        $this->maxRetries = 3;
        $this->retryDelay = 1000; // 1 seconde
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
            'timeout' => 30, // 30 secondes de timeout
            'max_duration' => 60, // 60 secondes max total
        ];

        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->maxRetries) {
            try {
                $response = $this->client->request('GET', $url, $options);
                return $response->toArray();
            } catch (TransportExceptionInterface $e) {
                $lastException = $e;
                $attempts++;
                
                // Log de l'erreur
                error_log(sprintf(
                    "Tentative %d/%d échouée pour %s: %s",
                    $attempts,
                    $this->maxRetries,
                    $url,
                    $e->getMessage()
                ));

                if ($attempts < $this->maxRetries) {
                    // Attendre avant de réessayer
                    usleep($this->retryDelay * 1000);
                    // Augmenter le délai pour la prochaine tentative
                    $this->retryDelay *= 2;
                }
            }
        }

        // Si toutes les tentatives ont échoué, on retourne un tableau vide
        // et on log l'erreur finale
        error_log(sprintf(
            "Toutes les tentatives ont échoué pour %s. Dernière erreur: %s",
            $url,
            $lastException ? $lastException->getMessage() : 'Unknown error'
        ));

        return [];
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
        
        $result = $this->request('/monster-races/' . $raceId);
        
        // Si la requête échoue, on retourne 'Inconnue'
        if (empty($result)) {
            return 'Inconnue';
        }
        
        return $result['name']['fr'] ?? 'Inconnue';
    }

    public function getMonsterSuperRaceName(?int $superRaceId): ?string
    {
        if (!$superRaceId) return 'Inconnue';
        
        $result = $this->request('/monster-super-races/' . $superRaceId);
        
        // Si la requête échoue, on retourne 'Inconnue'
        if (empty($result)) {
            return 'Inconnue';
        }
        
        return $result['name']['fr'] ?? 'Inconnue';
    }

    public function getSubareaName(int $subareaId): ?string
    {
        if (!$subareaId) return 'Inconnue';
        
        $result = $this->request('/subareas/' . $subareaId);
        
        // Si la requête échoue, on retourne 'Inconnue'
        if (empty($result)) {
            return 'Inconnue';
        }
        
        return $result['name']['fr'] ?? 'Inconnue';
    }

    public function getAreaName(int $areaId): ?string
    {
        if (!$areaId) return 'Inconnue';
        
        $result = $this->request('/areas/' . $areaId);
        
        // Si la requête échoue, on retourne 'Inconnue'
        if (empty($result)) {
            return 'Inconnue';
        }
        
        return $result['name']['fr'] ?? 'Inconnue';
    }

    public function getSubareaNamesAndAreas(array $subareaIds): array
    {
        $subareas = [];
        $areas = [];
        foreach ($subareaIds as $id) {
            $sub = $this->request('/subareas/' . $id);
            if (!empty($sub)) {
                $subareas[] = $sub['name']['fr'] ?? null;
                if (isset($sub['areaId'])) {
                    $area = $this->request('/areas/' . $sub['areaId']);
                    if (!empty($area)) {
                        $areas[] = $area['name']['fr'] ?? null;
                    }
                }
            }
        }
        return ['subareas' => array_filter($subareas), 'areas' => array_filter($areas)];
    }

    public function getMiniBossName(?int $miniBossId): ?string
    {
        if (!$miniBossId) return null;
        
        $result = $this->request('/monsters/' . $miniBossId);
        
        // Si la requête échoue, on retourne null
        if (empty($result)) {
            return null;
        }
        
        return $result['name']['fr'] ?? null;
    }
}
