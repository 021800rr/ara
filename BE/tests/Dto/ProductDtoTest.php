<?php

namespace App\Tests\Dto;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\ProductDto;
use App\Tests\SetUpTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ProductDtoTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpValidator();
    }

    #[DataProvider('priceProvider')]
    public function testProductDto(null|float|int $price, int $expectedViolationCount): void
    {
        $dto = new ProductDto();
        $dto->name = 'Test Product';
        $dto->description = 'Test Description';
        $dto->price = $price;

        $violations = $this->validator->validate($dto);

        $this->assertCount($expectedViolationCount, $violations);
    }

    /**
     * @return array<int, array{0: int|float|null, 1: int}>
     */
    public static function priceProvider(): array
    {
        return [
            [10, 0], // Valid case
            [11.1, 0], // Valid case
            [null, 1], // Missing price
        ];
    }
}
