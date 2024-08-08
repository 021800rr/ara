<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CartRepository;
use App\State\CartProcessor;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/carts/{id}',
            normalizationContext: ['groups' => ['cart:read']],
        ),
        new GetCollection(
            uriTemplate: '/carts',
            normalizationContext: ['groups' => ['cart:read']],
        ),
        new Post(
            uriTemplate: '/carts',
            denormalizationContext: ['groups' => ['cart:write']],
            processor: CartProcessor::class,
        ),
        new Put(
            uriTemplate: '/carts/{id}',
            denormalizationContext: ['groups' => ['cart:write']],
            security: 'is_granted("' . User::ROLE_USER . '") and object.getUser() == user',
            processor: CartProcessor::class,
        ),
    ],
    security: 'is_granted("' . User::ROLE_USER . '")'
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt'])]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cart:read'])]
    private ?User $user = null;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(
        targetEntity: CartItem::class,
        mappedBy: "cart",
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    #[Groups(['cart:read', 'cart:write'])]
    #[Assert\Type(Collection::class)]
    private Collection $items;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['cart:read'])]
    #[Assert\NotBlank]
    #[Assert\Type(DateTimeImmutable::class)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cart:read'])]
    #[Assert\Type(DateTime::class)]
    private ?DateTime $updatedAt = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    #[Groups(['cart:read'])]
    public function getTotalValue(): float
    {
        $totalValue = 0.0;

        foreach ($this->items as $item) {
            $totalValue += $item->getPrice() * $item->getQuantity();
        }

        return $totalValue;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCart($this);
        }

        return $this;
    }

    public function removeItem(CartItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
