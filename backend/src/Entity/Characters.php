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

    #[ORM\Column(type: 'boolean')]
    private bool $isArchived = false;

    

    // Getters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function getAnkamaPseudo(): string
    {
        return $this->ankamaPseudo;
    }

    public function getClass(): string
    {
        return $this->class;
    }


    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    // Setters

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function setAnkamaPseudo(string $ankamaPseudo): self
    {
        $this->ankamaPseudo = $ankamaPseudo;
        return $this;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;
        return $this;
    }


    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('today', message: 'The creation date cannot be in the future.')]
    private \DateTimeInterface $recruitedAt;

    public function getRecruitedAt(): \DateTimeInterface
    {
        return $this->recruitedAt;
    }

    public function setRecruitedAt(\DateTimeInterface $recruitedAt): self
    {
        $this->recruitedAt = $recruitedAt;
        return $this;
    }



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
