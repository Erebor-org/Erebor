<?php

namespace App\Entity;

use App\Repository\BlacklistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * Autres personnages (mules/alts) sur lesquels la personne blacklistée peut se connecter.
     *
     * @var Collection<int, BlacklistCharacter>
     */
    #[ORM\OneToMany(mappedBy: 'blacklist', targetEntity: BlacklistCharacter::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $associatedCharacters;

    public function __construct()
    {
        $this->associatedCharacters = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, BlacklistCharacter>
     */
    public function getAssociatedCharacters(): Collection
    {
        return $this->associatedCharacters;
    }

    public function addAssociatedCharacter(BlacklistCharacter $character): self
    {
        if (!$this->associatedCharacters->contains($character)) {
            $this->associatedCharacters->add($character);
            $character->setBlacklist($this);
        }
        return $this;
    }

    public function removeAssociatedCharacter(BlacklistCharacter $character): self
    {
        if ($this->associatedCharacters->removeElement($character)) {
            if ($character->getBlacklist() === $this) {
                $character->setBlacklist(null);
            }
        }
        return $this;
    }
}
