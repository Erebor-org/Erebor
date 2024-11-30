<?php

namespace App\Entity;

use App\Repository\CharactersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharactersRepository::class)]
#[ORM\Table(name: "characters")]
class Characters
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $pseudo;

    #[ORM\Column(type: 'string', length: 255)]
    private string $ankamaPseudo;

    #[ORM\Column(type: 'string', length: 255)]
    private string $class;

    #[ORM\Column(type: 'integer')]
    private int $level;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('today', message: 'The creation date cannot be in the future.')]
    private \DateTimeInterface $recruitedAt;

    public function getrecruitedAt(): \DateTimeInterface
    {
        return $this->recruitedAt;
    }

    public function setrecruitedAt(\DateTimeInterface $recruitedAt): self
    {
        $this->recruitedAt = $recruitedAt;
        return $this;
    }

    #[ORM\Column(type: 'boolean')]
    private bool $isArchived = false;

    #[ORM\ManyToOne(targetEntity: Ranks::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Ranks $rank;

    // Getters and Setters
    public function getRank(): Ranks
    {
        return $this->rank;
    }

    public function setRank(Ranks $rank): self
    {
        $this->rank = $rank;
        return $this;
    }
    
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: "recruiter_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Characters $recruiter = null;

    public function getRecruiter(): ?Characters
    {
        return $this->recruiter;
    }

    public function setRecruiter(?Characters $recruiter): self
    {
        $this->recruiter = $recruiter;
        return $this;
    }
}
