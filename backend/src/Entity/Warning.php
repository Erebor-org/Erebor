<?php

namespace App\Entity;

use App\Repository\WarningRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WarningRepository::class)]
#[ORM\Table(name: "warnings")]
class Warning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Characters::class)]
    #[ORM\JoinColumn(name: "character_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Characters $character;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Description cannot be blank")]
    #[Assert\Length(max: 1000, maxMessage: "Description cannot be longer than {{ limit }} characters")]
    private string $description;

    #[ORM\ManyToOne(targetEntity: Characters::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Characters $authorCharacter = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
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

    public function getAuthorCharacter(): ?Characters
    {
        return $this->authorCharacter;
    }

    public function setAuthorCharacter(?Characters $authorCharacter): self
    {
        $this->authorCharacter = $authorCharacter;
        return $this;
    }
}
