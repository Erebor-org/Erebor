<?php

namespace App\Entity;

use App\Repository\EventParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventParticipantRepository::class)]
#[ORM\Table(name: 'event_participants')]
class EventParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Event $event;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rank = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $pointsEarned = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prizeReceived = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $subscribedAt;

    public function __construct()
    {
        $this->subscribedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;
        return $this;
    }

    public function getPointsEarned(): ?string
    {
        return $this->pointsEarned;
    }

    public function setPointsEarned(?string $pointsEarned): self
    {
        $this->pointsEarned = $pointsEarned;
        return $this;
    }

    public function getPrizeReceived(): ?string
    {
        return $this->prizeReceived;
    }

    public function setPrizeReceived(?string $prizeReceived): self
    {
        $this->prizeReceived = $prizeReceived;
        return $this;
    }

    public function getSubscribedAt(): \DateTimeInterface
    {
        return $this->subscribedAt;
    }

    public function setSubscribedAt(\DateTimeInterface $subscribedAt): self
    {
        $this->subscribedAt = $subscribedAt;
        return $this;
    }
}





