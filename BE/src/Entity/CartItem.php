<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\State\ItemProcessor;
use App\Validator\Constraints\CartItemCount;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CartItemRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ApiResource(
    operations: [
        new Delete(
            uriTemplate: '/items/{id}',
            security: 'is_granted("' . User::ROLE_USER . '") and object.getCart().getUser() == user',
            processor: ItemProcessor::class,
        ),
    ],
)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cart:read', 'cart:write'])]
    #[Assert\NotNull]
    #[Assert\Type(Product::class)]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type(Cart::class)]
    #[CartItemCount]
    private ?Cart $cart = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['cart:read', 'cart:write'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type(Types::INTEGER)]
    private ?int $quantity;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['cart:read'])]
    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    #[Assert\Positive]
    private null|float|int $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): null|float|int
    {
        return $this->price;
    }

    public function setPrice(null|float|int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
