<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'events')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $cashPrize = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'integer')]
    private int $ownerId;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isFinished = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventParticipant::class, cascade: ['persist', 'remove'])]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getCashPrize(): ?string
    {
        return $this->cashPrize;
    }

    public function setCashPrize(?string $cashPrize): self
    {
        $this->cashPrize = $cashPrize;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): self
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): self
    {
        $this->isFinished = $isFinished;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, EventParticipant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(EventParticipant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setEvent($this);
        }
        return $this;
    }

    public function removeParticipant(EventParticipant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            if ($participant->getEvent() === $this) {
                $participant->setEvent(null);
            }
        }
        return $this;
    }
}




