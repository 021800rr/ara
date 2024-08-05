<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\ProductDto;
use App\Entity\Product;
use App\State\ProductProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductProcessorTest extends TestCase
{
    private ProductProcessor $productProcessor;

    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productProcessor = new ProductProcessor($this->entityManager);
    }

    public function testPostProcessValidProductDto(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Test Product';
        $productDto->description = 'Test Description';
        $productDto->price = 10.1;

        $operation = new Post();

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($product) use ($productDto) {
                return $product instanceof Product &&
                    $product->getName() === $productDto->name &&
                    $product->getDescription() === $productDto->description &&
                    $product->getPrice() === $productDto->price;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->productProcessor->process($productDto, $operation);
    }

    public function testPutProcessValidProductDto(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Updated Product';
        $productDto->description = 'Updated Description';
        $productDto->price = 110.1;

        $operation = new Put();
        $uriVariables = ['id' => 1];

        $existingProduct = new Product();
        $existingProduct->setName('Old Product');
        $existingProduct->setDescription('Old Description');

        $productRepository = $this->createMock(EntityRepository::class);
        $productRepository->expects($this->once())
            ->method('find')
            ->with($uriVariables['id'])
            ->willReturn($existingProduct);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepository);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($product) use ($productDto) {
                return $product instanceof Product &&
                    $product->getName() === $productDto->name &&
                    $product->getDescription() === $productDto->description &&
                    $product->getPrice() === $productDto->price;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->productProcessor->process($productDto, $operation, $uriVariables);
    }

    public function testPostProcessWithMissingPrice(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Test Product';
        $productDto->description = 'Test Description';
        $productDto->price = null;

        $operation = new Post();

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($product) use ($productDto) {
                return $product instanceof Product &&
                    $product->getName() === $productDto->name &&
                    $product->getDescription() === $productDto->description &&
                    empty($product->getPrice());
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->productProcessor->process($productDto, $operation);
    }

    public function testProcessInvalidProductDto(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of ProductDto');

        $operation = $this->createMock(Operation::class);
        $invalidData = new \stdClass();

        // @phpstan-ignore-next-line
        $this->productProcessor->process($invalidData, $operation);
    }

    public function testPutProcessNonExistentProduct(): void
    {
        $productDto = new ProductDto();
        $productDto->name = 'Non Existent Product';
        $productDto->description = 'Non Existent Description';
        $productDto->price = 110.1;

        $operation = new Put();
        $uriVariables = ['id' => 999];

        $productRepository = $this->createMock(EntityRepository::class);
        $productRepository->expects($this->once())
            ->method('find')
            ->with($uriVariables['id'])
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepository);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product not found');

        $this->productProcessor->process($productDto, $operation, $uriVariables);
    }
}
