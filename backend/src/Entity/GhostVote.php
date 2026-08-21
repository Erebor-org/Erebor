<?php

namespace App\Entity;

use App\Repository\GhostVoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GhostVoteRepository::class)]
#[ORM\Table(name: "ghost_vote")]
#[ORM\UniqueConstraint(name: "uniq_ghost_vote_round_character_voter", columns: ["round_id", "character_id", "voter_id"])]
class GhostVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GhostRound::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?GhostRound $round = null;

    #[ORM\ManyToOne(targetEntity: Characters::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Characters $character;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $voter;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRound(): ?GhostRound
    {
        return $this->round;
    }

    public function setRound(?GhostRound $round): self
    {
        $this->round = $round;
        return $this;
    }

    public function getCharacter(): Characters
    {
        return $this->character;
    }

    public function setCharacter(Characters $character): self
    {
        $this->character = $character;
        return $this;
    }

    public function getVoter(): User
    {
        return $this->voter;
    }

    public function setVoter(User $voter): self
    {
        $this->voter = $voter;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
