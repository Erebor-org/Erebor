<?php

namespace App\Entity;

use App\Repository\BlacklistCharacterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlacklistCharacterRepository::class)]
#[ORM\Table(name: "blacklist_character")]
class BlacklistCharacter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ankamaPseudo = null;

    #[ORM\ManyToOne(targetEntity: Blacklist::class, inversedBy: 'associatedCharacters')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Blacklist $blacklist = null;

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

    public function setAnkamaPseudo(?string $ankamaPseudo): self
    {
        $this->ankamaPseudo = $ankamaPseudo;
        return $this;
    }

    public function getBlacklist(): ?Blacklist
    {
        return $this->blacklist;
    }

    public function setBlacklist(?Blacklist $blacklist): self
    {
        $this->blacklist = $blacklist;
        return $this;
    }
}
