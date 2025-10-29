<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: '`user`')]  // <-- This escapes "user" to avoid conflict with PostgreSQL reserved keyword
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    private string $username;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', nullable: true, options: ["default" => "user"])]
    private ?string $rank = 'user';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $characterId = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $forceDisconnectAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles ?: ['ROLE_USER'];
        
        // Ensure ROLE_USER is always included
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        
        // Symfony Security expects at least ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRank(): ?string
    {
        return $this->rank;
    }

    public function setRank(?string $rank): self
    {
        $this->rank = $rank;
        return $this;
    }

    public function getCharacterId(): ?int
    {
        return $this->characterId;
    }

    public function setCharacterId(?int $characterId): self
    {
        $this->characterId = $characterId;
        return $this;
    }

    public function getForceDisconnectAt(): ?\DateTime
    {
        return $this->forceDisconnectAt;
    }

    public function setForceDisconnectAt(?\DateTime $forceDisconnectAt): self
    {
        $this->forceDisconnectAt = $forceDisconnectAt;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        // If you store temporary sensitive data, clear it here
    }
    public function getSalt() {}

}
