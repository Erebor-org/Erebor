<?php

namespace App\Entity;

use App\Repository\MuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MuleRepository::class)]
#[ORM\Table(name: "mules")]
class Mule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $pseudo;

    #[ORM\Column(type: 'string', length: 255)]
    private string $ankamaPseudo;

    #[ORM\Column(type: 'string', length: 255)]
    private string $class;

    #[ORM\Column(type: 'boolean')]
    private bool $isArchived = false;

    #[ORM\ManyToOne(targetEntity: Characters::class, inversedBy: 'mules')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Characters $mainCharacter = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getAnkamaPseudo(): string
    {
        return $this->ankamaPseudo;
    }

    public function setAnkamaPseudo(string $ankamaPseudo): self
    {
        $this->ankamaPseudo = $ankamaPseudo;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function getMainCharacter(): ?Characters
    {
        return $this->mainCharacter;
    }

    public function setMainCharacter(?Characters $mainCharacter): self
    {
        $this->mainCharacter = $mainCharacter;
        return $this;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;
        return $this;
    }
}
