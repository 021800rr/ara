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
        $product->setPrice('USD', 100);
        $product->setPrice('EUR', 90.1);

        $this->productRepository->save($product, true);

        $savedProduct = $this->productRepository->find($product->getId());

        $this->assertNotNull($savedProduct);
        $this->assertEquals('Test Product', $savedProduct->getName());
        $this->assertEquals('Test Description', $savedProduct->getDescription());
        $this->assertEquals(100.0, $savedProduct->getPrice('USD'));
        $this->assertEquals(90.1, $savedProduct->getPrice('EUR'));
    }

    public function testRemoveProduct(): void
    {
        /** @var Product $product */
        $product = $this->productRepository->find(1);
        $this->productRepository->remove($product, true);

        $removedProduct = $this->productRepository->find(1);

        $this->assertNull($removedProduct);
    }
}
