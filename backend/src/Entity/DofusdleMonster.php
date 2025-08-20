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

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $levelMin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $levelMax = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $hpMin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $hpMax = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $apMin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $apMax = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $mpMin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $mpMax = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $resistancesMax = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $race = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $superArea = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $area = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subarea = null;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $dominantColor = null;

    // Getters et setters
    public function getId(): ?int { return $this->id; }
    
    public function getDofusdbId(): int { return $this->dofusdbId; }
    public function setDofusdbId(int $id): self { $this->dofusdbId = $id; return $this; }
    
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): self { $this->imageUrl = $imageUrl; return $this; }
    
    public function getLevelMin(): ?int { return $this->levelMin; }
    public function setLevelMin(?int $levelMin): self { $this->levelMin = $levelMin; return $this; }
    
    public function getLevelMax(): ?int { return $this->levelMax; }
    public function setLevelMax(?int $levelMax): self { $this->levelMax = $levelMax; return $this; }
    
    public function getHpMin(): ?int { return $this->hpMin; }
    public function setHpMin(?int $hpMin): self { $this->hpMin = $hpMin; return $this; }
    
    public function getHpMax(): ?int { return $this->hpMax; }
    public function setHpMax(?int $hpMax): self { $this->hpMax = $hpMax; return $this; }
    
    public function getApMin(): ?int { return $this->apMin; }
    public function setApMin(?int $apMin): self { $this->apMin = $apMin; return $this; }
    
    public function getApMax(): ?int { return $this->apMax; }
    public function setApMax(?int $apMax): self { $this->apMax = $apMax; return $this; }
    
    public function getMpMin(): ?int { return $this->mpMin; }
    public function setMpMin(?int $mpMin): self { $this->mpMin = $mpMin; return $this; }
    
    public function getMpMax(): ?int { return $this->mpMax; }
    public function setMpMax(?int $mpMax): self { $this->mpMax = $mpMax; return $this; }
    
    public function getResistancesMax(): ?array { return $this->resistancesMax; }
    public function setResistancesMax(?array $resistancesMax): self { $this->resistancesMax = $resistancesMax; return $this; }
    
    public function getRace(): ?string { return $this->race; }
    public function setRace(?string $race): self { $this->race = $race; return $this; }
    
    public function getSuperArea(): ?string { return $this->superArea; }
    public function setSuperArea(?string $superArea): self { $this->superArea = $superArea; return $this; }

    public function getArea(): ?string { return $this->area; }
    public function setArea(?string $area): self { $this->area = $area; return $this; }

    public function getSubarea(): ?string { return $this->subarea; }
    public function setSubarea(?string $subarea): self { $this->subarea = $subarea; return $this; }
    
    public function getDominantColor(): ?string { return $this->dominantColor; }
    public function setDominantColor(?string $dominantColor): self { $this->dominantColor = $dominantColor; return $this; }
}
