<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Config\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource]
#[Get(
    normalizationContext: ['groups' => ['user:get']],
    security: 'is_granted("' . User::ROLE_ADMIN . '")',
)]
#[GetCollection(
    normalizationContext: ['groups' => ['user:get']],
    security: 'is_granted("' . User::ROLE_ADMIN . '")',
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'lastName', 'status', 'email'])]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'ipartial', 'name' => 'ipartial', 'lastName' => 'ipartial'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLES = [
        self::ROLE_EDITOR,
        self::ROLE_ADMIN,
    ];

    private const STATUSES = [UserStatus::inactive->name, UserStatus::active->name, UserStatus::deleted->name];

    #[Groups(['user:get'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups(['user:get'])]
    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private ?string $name = null;

    #[Groups(['user:get'])]
    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private ?string $lastName = null;

    #[Groups(['user:get'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Email(mode: 'html5')]
    #[Assert\Length(min: 5, max: 255)] // a@b.x
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[Groups(['user:get'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::ROLES, multiple: true)]
    private array $roles = [];

    #[Groups(['user:get'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUSES, message: 'Choose a valid status.')]
    private ?string $status = UserStatus::inactive->name;

    #[ORM\Column]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    // UserStatus::[inactive|active|deleted]->name;
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }
}
