<?php

namespace App\Tests\Repository;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Product;
use App\Tests\SetUpTrait;

class ProductRepositoryTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpRepositories();
    }

    public function testSaveProduct(): void
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setDescription('Test Description');
        $product->setPrice(100);

        $this->productRepository->save($product, true);

        $savedProduct = $this->productRepository->find($product->getId());

        $this->assertNotNull($savedProduct);
        $this->assertEquals('Test Product', $savedProduct->getName());
        $this->assertEquals('Test Description', $savedProduct->getDescription());
        $this->assertEquals(100, $savedProduct->getPrice());
    }

    public function testRemoveProduct(): void
    {
        /** @var Product $product */
        $product = $this->productRepository->find(4);
        $this->productRepository->remove($product, true);

        $removedProduct = $this->productRepository->find(4);

        $this->assertNull($removedProduct);
    }
}
