<?php

namespace App\Entity;

use App\Repository\Gw2LegendaryTrackingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A legendary a user follows in the private Guild Wars 2 section.
 */
#[ORM\Entity(repositoryClass: Gw2LegendaryTrackingRepository::class)]
#[ORM\Table(name: "gw2_legendary_tracking")]
#[ORM\UniqueConstraint(name: "uniq_gw2_legendary_tracking_user_item", columns: ["user_id", "item_id"])]
class Gw2LegendaryTracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    // GW2 item id of the legendary
    #[ORM\Column(type: 'integer')]
    private int $itemId;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct(User $user, int $itemId)
    {
        $this->user = $user;
        $this->itemId = $itemId;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
