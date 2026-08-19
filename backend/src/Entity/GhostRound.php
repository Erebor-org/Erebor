<?php

namespace App\Entity;

use App\Repository\GhostRoundRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: GhostRoundRepository::class)]
#[ORM\Table(name: "ghost_round")]
class GhostRound
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $openedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $closedBy = null;

    #[ORM\Column(type: 'integer')]
    private int $threshold;

    #[ORM\OneToMany(mappedBy: 'round', targetEntity: GhostVote::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $votes;

    public function __construct()
    {
        $this->openedAt = new \DateTime();
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenedAt(): \DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(\DateTimeInterface $openedAt): self
    {
        $this->openedAt = $openedAt;
        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;
        return $this;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }

    public function setClosedBy(?User $closedBy): self
    {
        $this->closedBy = $closedBy;
        return $this;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function setThreshold(int $threshold): self
    {
        $this->threshold = $threshold;
        return $this;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(GhostVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setRound($this);
        }
        return $this;
    }

    public function removeVote(GhostVote $vote): self
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getRound() === $this) {
                $vote->setRound(null);
            }
        }
        return $this;
    }
}
