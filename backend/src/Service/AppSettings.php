<?php

namespace App\Service;

use App\Entity\AppSetting;
use App\Repository\AppSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Point d'entrée unique des réglages applicatifs : liste blanche des clés, valeurs par défaut
 * et validation. Une clé absente en base renvoie sa valeur par défaut.
 */
class AppSettings
{
    /** Ce qu'un ROLE_ADMIN (Enroleur) voit du dashboard recrutement. */
    public const RECRUITMENT_ADMIN_SCOPE = 'recruitment.admin_scope';
    public const SCOPE_NONE = 'none';
    public const SCOPE_OWN = 'own';
    public const SCOPE_ALL = 'all';

    private const DEFINITIONS = [
        self::RECRUITMENT_ADMIN_SCOPE => [
            'default' => self::SCOPE_OWN,
            'allowed' => [self::SCOPE_NONE, self::SCOPE_OWN, self::SCOPE_ALL],
        ],
    ];

    public function __construct(
        private readonly AppSettingRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return array<string, mixed> toutes les clés connues avec leur valeur effective */
    public function all(): array
    {
        $values = [];
        foreach (array_keys(self::DEFINITIONS) as $key) {
            $values[$key] = $this->get($key);
        }
        return $values;
    }

    public function get(string $key): mixed
    {
        $definition = $this->definition($key);
        $stored = $this->repository->find($key)?->getValue();

        return in_array($stored, $definition['allowed'], true) ? $stored : $definition['default'];
    }

    /** @throws \InvalidArgumentException clé inconnue ou valeur non autorisée */
    public function set(string $key, mixed $value): void
    {
        $definition = $this->definition($key);
        if (!in_array($value, $definition['allowed'], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Valeur invalide pour "%s" (attendu : %s).',
                $key,
                implode(', ', $definition['allowed'])
            ));
        }

        $setting = $this->repository->find($key);
        if ($setting) {
            $setting->setValue($value);
        } else {
            $this->em->persist(new AppSetting($key, $value));
        }
        $this->em->flush();
    }

    /** @return array<string, list<mixed>> */
    public function allowedValues(): array
    {
        return array_map(fn (array $d) => $d['allowed'], self::DEFINITIONS);
    }

    private function definition(string $key): array
    {
        if (!isset(self::DEFINITIONS[$key])) {
            throw new \InvalidArgumentException(sprintf('Réglage inconnu : "%s".', $key));
        }
        return self::DEFINITIONS[$key];
    }
}
