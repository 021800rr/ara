<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ProductDto;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<ProductDto, void>
 */
final readonly class ProductProcessor implements ProcessorInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof ProductDto) {
            throw new \InvalidArgumentException('Expected instance of ProductDto');
        }

        $product = null;

        if ($operation instanceof Post) {
            $product = new Product();
        } elseif ($operation instanceof Put && $uriVariables['id']) {
            $product = $this->entityManager->getRepository(Product::class)->find($uriVariables['id']);
            if (!$product) {
                throw new \RuntimeException('Product not found');
            }
        }

        if ($product === null) {
            throw new \InvalidArgumentException('Unsupported operation');
        }

        $product->setName($data->name);
        $product->setDescription($data->description);

        $product->setPrice($data->price);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}
