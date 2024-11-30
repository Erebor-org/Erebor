<?php

namespace App\Entity;

use App\Repository\RanksRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RanksRepository::class)]
#[ORM\Table(name: "ranks")]
class Ranks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $requiredDays = null; // Set as nullable

    #[ORM\Column(type: 'text')]
    private string $description;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

     // Getters and Setters...

     public function getRequiredDays(): ?int
     {
         return $this->requiredDays;
     }
 
     public function setRequiredDays(?int $requiredDays): self
     {
         $this->requiredDays = $requiredDays;
         return $this;
     }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    public function findRankForDays(int $days): ?Ranks
    {
        return $this->createQueryBuilder('r')
            ->where('r.requiredDays <= :days')
            ->setParameter('days', $days)
            ->orderBy('r.requiredDays', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    #[ORM\Column(type: 'boolean')]
    private bool $lead = false;

    #[ORM\Column(type: 'boolean')]
    private bool $recruiter = false;

    // Getters and setters...

    public function getLead(): bool
    {
        return $this->lead;
    }

    public function setLead(bool $lead): self
    {
        $this->lead = $lead;
        return $this;
    }

    public function getRecruiter(): bool
    {
        return $this->recruiter;
    }

    public function setRecruiter(bool $recruiter): self
    {
        $this->recruiter = $recruiter;
        return $this;
    }
    #[ORM\Column(type: 'boolean')]
    private bool $needUpdate = false; // New boolean field

    // Getters and Setters...

    public function getNeedUpdate(): bool
    {
        return $this->needUpdate;
    }

    public function setNeedUpdate(bool $needUpdate): self
    {
        $this->needUpdate = $needUpdate;
        return $this;
    }

}
