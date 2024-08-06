<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\ProductDto;
use App\Repository\ProductRepository;
use App\State\ProductProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/products/{id}',
            normalizationContext: ['groups' => ['product:read']],
            security: 'is_granted("' . User::ROLE_USER . '")',
        ),
        new GetCollection(
            uriTemplate: '/products',
            paginationItemsPerPage: 3,
            normalizationContext: ['groups' => ['product:read']],
            security: 'is_granted("' . User::ROLE_USER . '")',
        ),
        new Post(
            uriTemplate: '/products',
            denormalizationContext: ['groups' => ['product:write']],
            security: 'is_granted("' . User::ROLE_EDITOR . '")',
            input: ProductDto::class,
            processor: ProductProcessor::class,
        ),
        new Put(
            uriTemplate: '/products/{id}',
            denormalizationContext: ['groups' => ['product:write']],
            security: 'is_granted("' . User::ROLE_EDITOR . '")',
            input: ProductDto::class,
            processor: ProductProcessor::class,
        ),
        new Delete(
            uriTemplate: '/products/{id}',
            security: 'is_granted("' . User::ROLE_EDITOR . '")',
        )
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'description'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'ipartial', 'description' => 'ipartial'])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type(type: Types::STRING)]
    #[Groups(['product:read', 'product:write'])]
    private ?string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Type(type: Types::STRING)]
    #[Groups(['product:read', 'product:write'])]
    private ?string $description = null;


    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    #[Groups(['product:read', 'product:write'])]
    private null|float|int $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
