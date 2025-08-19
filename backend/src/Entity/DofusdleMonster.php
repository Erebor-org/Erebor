<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DofusdleMonster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', unique: true)]
    private int $dofusdbId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $pdv = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $family = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $level = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isBoss = false;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $element = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $pa = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $pm = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $superRace = null;
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $subareas = null;
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $areas = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $favoriteSubareaId = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $bossType = null;
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tags = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $spellsCount = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $correspondingMiniBossId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $correspondingMiniBossName = null;

    // Getters et setters ...
    public function getId(): ?int { return $this->id; }
    public function getDofusdbId(): int { return $this->dofusdbId; }
    public function setDofusdbId(int $id): self { $this->dofusdbId = $id; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getPdv(): ?int { return $this->pdv; }
    public function setPdv(?int $pdv): self { $this->pdv = $pdv; return $this; }
    public function getFamily(): ?string { return $this->family; }
    public function setFamily(?string $family): self { $this->family = $family; return $this; }
    public function getLevel(): ?int { return $this->level; }
    public function setLevel(?int $level): self { $this->level = $level; return $this; }
    public function isBoss(): bool { return $this->isBoss; }
    public function setIsBoss(bool $isBoss): self { $this->isBoss = $isBoss; return $this; }
    public function getElement(): ?string { return $this->element; }
    public function setElement(?string $element): self { $this->element = $element; return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
    public function getPa(): ?int { return $this->pa; }
    public function setPa(?int $pa): self { $this->pa = $pa; return $this; }
    public function getPm(): ?int { return $this->pm; }
    public function setPm(?int $pm): self { $this->pm = $pm; return $this; }
    public function getSuperRace(): ?string { return $this->superRace; }
    public function setSuperRace(?string $v): self { $this->superRace = $v; return $this; }
    public function getSubareas(): ?array { return $this->subareas; }
    public function setSubareas(?array $v): self { $this->subareas = $v; return $this; }
    public function getAreas(): ?array { return $this->areas; }
    public function setAreas(?array $v): self { $this->areas = $v; return $this; }
    public function getFavoriteSubareaId(): ?int { return $this->favoriteSubareaId; }
    public function setFavoriteSubareaId(?int $v): self { $this->favoriteSubareaId = $v; return $this; }
    public function getBossType(): ?string { return $this->bossType; }
    public function setBossType(?string $v): self { $this->bossType = $v; return $this; }
    public function getTags(): ?array { return $this->tags; }
    public function setTags(?array $v): self { $this->tags = $v; return $this; }
    public function getSpellsCount(): ?int { return $this->spellsCount; }
    public function setSpellsCount(?int $v): self { $this->spellsCount = $v; return $this; }
    public function getCorrespondingMiniBossId(): ?int { return $this->correspondingMiniBossId; }
    public function setCorrespondingMiniBossId(?int $v): self { $this->correspondingMiniBossId = $v; return $this; }
    public function getCorrespondingMiniBossName(): ?string { return $this->correspondingMiniBossName; }
    public function setCorrespondingMiniBossName(?string $v): self { $this->correspondingMiniBossName = $v; return $this; }
}
