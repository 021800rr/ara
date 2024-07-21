<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\ProductDto;
use App\Entity\Product;
use App\Entity\User;
use App\Tests\SetUpTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ProductTest extends ApiTestCase
{
    use SetUpTrait;

    private const string PRODUCTS_URL = '/api/products';
    private const array HEADERS = ['Content-Type' => 'application/ld+json'];
    private const array NEW_PRODUCT = [
        'name' => 'New Product',
        'description' => 'Product Description',
        'prices' => ['USD' => 100.1, 'EUR' => 90]
    ];
    private const array UPDATED_PRODUCT = [
        'name' => 'Updated Product',
        'description' => 'Updated Description',
        'prices' => ['USD' => 110.1, 'EUR' => 100]
    ];
    private const string PLAIN_PASSWORD = 'test';

    private string $editorToken;
    private string $userToken;

    protected function setUp(): void
    {
        $this->setUpRepositories();
        $this->setUpValidator();
        /** @var User $editor */
        $editor = $this->userRepository->find(2);
        /** @var User $user */
        $user = $this->userRepository->find(3);
        $this->editorToken = $this->login((string) $editor->getEmail(), self::PLAIN_PASSWORD);
        $this->userToken = $this->login((string) $user->getEmail(), self::PLAIN_PASSWORD);
    }

    /** @param array<string, float|int> $prices */
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
            [['USD' => 10.1, 'EUR' => 9], 0], // Valid case
            [['USD' => 'invalid', 'EUR' => null], 2], // Invalid prices
            [[], 1], // Missing prices
            [['VND' => 23000], 1], // Unsupported currency (Vietnamese Dong)
        ];
    }

    public function testCreateProductWithValidDescription(): void
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setDescription(str_repeat('a', 10000));
        $product->setPrice('USD', 10.0);
        $product->setPrice('EUR', 9.0);

        $violations = $this->validator->validate($product);

        $this->assertCount(0, $violations);
    }

    public function testUserCanAccessProducts(): void
    {
        self::createClient()->request('GET', self::PRODUCTS_URL, [
            'auth_bearer' => $this->userToken,
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotCreateProduct(): void
    {
        self::createClient()->request('POST', self::PRODUCTS_URL, [
            'auth_bearer' => $this->userToken,
            'headers' => self::HEADERS,
            'json' => self::NEW_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditorCanCreateProduct(): void
    {
        self::createClient()->request('POST', self::PRODUCTS_URL, [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
            'json' => self::NEW_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testUserCannotUpdateProduct(): void
    {
        self::createClient()->request('PUT', self::PRODUCTS_URL . "/1", [
            'auth_bearer' => $this->userToken,
            'headers' => self::HEADERS,
            'json' => self::UPDATED_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditorCanUpdateProduct(): void
    {
        $client = self::createClient();

        $client->request('PUT', self::PRODUCTS_URL . "/1", [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
            'json' => self::UPDATED_PRODUCT,
        ]);

        $this->assertResponseStatusCodeSame(200);

        // Fetch the updated product to verify the changes
        $response = $client->request('GET', self::PRODUCTS_URL . "/1", [
            'auth_bearer' => $this->editorToken,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        $updatedProduct = $response->toArray();

        $this->assertEquals(self::UPDATED_PRODUCT['name'], $updatedProduct['name']);
        $this->assertEquals(self::UPDATED_PRODUCT['description'], $updatedProduct['description']);
        $this->assertEquals(self::UPDATED_PRODUCT['prices'], $updatedProduct['prices']);
    }

    public function testUserCannotDeleteProduct(): void
    {
        self::createClient()->request('DELETE', self::PRODUCTS_URL . "/1", [
            'auth_bearer' => $this->userToken,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditorCanDeleteProduct(): void
    {
        self::createClient()->request('DELETE', self::PRODUCTS_URL . "/1", [
            'auth_bearer' => $this->editorToken,
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
