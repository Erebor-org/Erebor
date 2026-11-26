<?php

namespace App\Entity;

use App\Repository\AppSettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Réglage applicatif clé → valeur JSON. Les clés autorisées et leurs valeurs valides sont
 * déclarées dans App\Service\AppSettings ; ne pas écrire de clé arbitraire ici.
 */
#[ORM\Entity(repositoryClass: AppSettingRepository::class)]
#[ORM\Table(name: "app_setting")]
class AppSetting
{
    #[ORM\Id]
    #[ORM\Column(name: '`key`', type: 'string', length: 64)]
    private string $key;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private mixed $value;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct(string $key, mixed $value)
    {
        $this->key = $key;
        $this->setValue($value);
    }

    public function getKey(): string { return $this->key; }
    public function getValue(): mixed { return $this->value; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }

    public function setValue(mixed $value): self
    {
        $this->value = $value;
        $this->updatedAt = new \DateTime();
        return $this;
    }
}
