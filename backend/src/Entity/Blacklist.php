<?php

namespace App\Entity;

use App\Repository\BlacklistRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlacklistRepository::class)]
class Blacklist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $ankamaPseudo = null;

    #[ORM\Column(type: 'text')]
    private ?string $reason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getAnkamaPseudo(): ?string
    {
        return $this->ankamaPseudo;
    }

    public function setAnkamaPseudo(string $ankamaPseudo): self
    {
        $this->ankamaPseudo = $ankamaPseudo;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }
}
