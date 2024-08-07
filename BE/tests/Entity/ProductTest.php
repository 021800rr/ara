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
        'price' => 100.1
    ];
    private const array UPDATED_PRODUCT = [
        'name' => 'Updated Product',
        'description' => 'Updated Description',
        'price' => 110.1
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
        $this->assertEquals(self::UPDATED_PRODUCT['price'], $updatedProduct['price']);
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

    /**
     * @param string[] $expectedNames
     */
    #[DataProvider('paginationProvider')]
    public function testProductPagination(int $page, int $expectedCount, array $expectedNames): void
    {
        $response = self::createClient()->request('GET', self::PRODUCTS_URL . '?page=' . $page, [
            'auth_bearer' => $this->userToken,
        ]);

        $this->assertResponseIsSuccessful();
        $products = $response->toArray()['hydra:member'];
        $this->assertCount($expectedCount, $products);

        foreach ($expectedNames as $index => $expectedName) {
            $this->assertEquals($expectedName, $products[$index]['name']);
        }
    }

    /**
     * @return array<int, array{0: int, 1: int, 2: string[]}>
     */
    public static function paginationProvider(): array
    {
        return [
            [1, 3, ['Product 1', 'Product 2', 'Product 3']], // First page
            [2, 1, ['Product 4']], // Second page
            [3, 0, []], // Third page
        ];
    }
}
