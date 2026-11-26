<?php

namespace App\Entity;

use App\Repository\GuildEventRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Journal des événements de guilde, alimenté automatiquement par GuildEventListener.
 *
 * Les pseudos sont recopiés pour que l'historique reste lisible après la suppression d'un
 * personnage (les clés étrangères passent alors à NULL). Les arrivées et les montées de rang
 * d'ancienneté ne sont pas journalisées : elles se déduisent de characters.recruited_at et
 * ranks.required_days, qui restent la seule source de vérité.
 */
#[ORM\Entity(repositoryClass: GuildEventRepository::class)]
#[ORM\Table(name: "guild_event")]
#[ORM\Index(name: "idx_guild_event_occurred_at", columns: ["occurred_at"])]
#[ORM\Index(name: "idx_guild_event_type", columns: ["type"])]
#[ORM\Index(name: "idx_guild_event_character", columns: ["character_id"])]
#[ORM\Index(name: "idx_guild_event_mule", columns: ["mule_id"])]
#[ORM\Index(name: "idx_guild_event_actor", columns: ["actor_user_id"])]
class GuildEvent
{
    /** Personnage archivé (départ). */
    public const LEFT = 'left';
    /** Personnage réintégré. */
    public const RETURNED = 'returned';
    /** Changement de rang fait sur le site (rang de fonction, correction manuelle). */
    public const RANK_CHANGED = 'rank_changed';
    public const MULE_ADDED = 'mule_added';
    public const MULE_LEFT = 'mule_left';
    public const MULE_RETURNED = 'mule_returned';
    /** Une mule devient le personnage principal. */
    public const MAIN_SWITCHED = 'main_switched';
    public const CHARACTER_DELETED = 'character_deleted';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $type;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $occurredAt;

    #[ORM\ManyToOne(targetEntity: Characters::class)]
    #[ORM\JoinColumn(name: "character_id", nullable: true, onDelete: "SET NULL")]
    private ?Characters $character = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $characterPseudo;

    #[ORM\ManyToOne(targetEntity: Mule::class)]
    #[ORM\JoinColumn(name: "mule_id", nullable: true, onDelete: "SET NULL")]
    private ?Mule $mule = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $mulePseudo = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "actor_user_id", nullable: true, onDelete: "SET NULL")]
    private ?User $actor = null;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $payload = [];

    public function __construct(string $type, Characters $character, ?\DateTimeInterface $occurredAt = null)
    {
        $this->type = $type;
        $this->character = $character;
        $this->characterPseudo = $character->getPseudo();
        $this->occurredAt = $occurredAt ?? new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getOccurredAt(): \DateTimeInterface { return $this->occurredAt; }
    public function getCharacter(): ?Characters { return $this->character; }
    public function getCharacterPseudo(): string { return $this->characterPseudo; }
    public function getMule(): ?Mule { return $this->mule; }
    public function getMulePseudo(): ?string { return $this->mulePseudo; }
    public function getActor(): ?User { return $this->actor; }
    public function getPayload(): array { return $this->payload; }

    /** Pour un personnage en cours de suppression : on garde le pseudo, pas la référence. */
    public function detachCharacter(): self
    {
        $this->character = null;
        return $this;
    }

    public function setMule(?Mule $mule): self
    {
        $this->mule = $mule;
        $this->mulePseudo = $mule?->getPseudo();
        return $this;
    }

    public function setActor(?User $actor): self
    {
        $this->actor = $actor;
        return $this;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }
}
