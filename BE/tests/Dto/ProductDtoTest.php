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

    /** @param array<string, float> $prices */
    #[DataProvider('pricesProvider')]
    public function testProductDto(array $prices, int $expectedViolationCount): void
    {
        $dto = new ProductDto();
        $dto->name = 'Test Product';
        $dto->description = 'Test Description';
        $dto->prices = $prices;

        $violations = $this->validator->validate($dto);

        $this->assertCount($expectedViolationCount, $violations);
    }

    /**
     * @return array<array{0: array<string, mixed>, 1: int}>
     */
    public static function pricesProvider(): array
    {
        return [
            [['USD' => 10, 'EUR' => 9.1], 0], // Valid case
            [['USD' => 'invalid', 'EUR' => null], 2], // Invalid prices
            [[], 1], // Missing prices
            [['VND' => 23000.0], 1], // Unsupported currency (Vietnamese Dong)
        ];
    }
}
