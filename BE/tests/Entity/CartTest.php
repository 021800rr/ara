<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Product;
use App\Tests\SetUpTrait;
use DateTime;
use App\Entity\Cart;

class CartTest extends ApiTestCase
{
    use SetUpTrait;

    private const array HEADERS = ['Content-Type' => 'application/ld+json'];
    private const string USER_MAIL = 'user@example.com';
    private const string ADMIN_MAIL = 'admin@example.com';
    private const string PLAIN_PASSWORD = 'test';
    private const int USER_ID = 3;
    private const string CARTS_URL = '/api/carts';
    private const string USERS_URL = '/api/users/';
    private const string PRODUCTS_URL = '/api/products/';

    protected function setUp(): void
    {
        $this->setUpRepositories();
    }

    public function testGet(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('GET', self::CARTS_URL . '/1', [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var object{id: int, user: string} $cart */
        $cart = json_decode($response->getContent());
        $this->assertSame(1, $cart->id);
        $this->assertSame(self::USERS_URL . self::USER_ID, $cart->user);
    }

    public function testGetNotFound(): void
    {
        $token = $this->login(self::ADMIN_MAIL, self::PLAIN_PASSWORD);

        static::createClient()->request('GET', self::CARTS_URL . '/1', [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetCollection(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('GET', self::CARTS_URL, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var array<"hydra:member", array<int, mixed>> $carts */
        $carts = json_decode($response->getContent(), true);
        $this->assertCount(2, $carts['hydra:member']);
    }

    public function testPost(): void
    {
        $now = new DateTime();
        sleep(2);
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('POST', self::CARTS_URL, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(201);

        /** @var object{id: int, user: string, createdAt: string} $cart */
        $cart = json_decode($response->getContent());

        $this->assertSame(self::USERS_URL . self::USER_ID, $cart->user);
        $this->assertGreaterThanOrEqual($now->format(DateTime::ATOM), $cart->createdAt);
    }

    public function testPut(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        $data = [
            "items" => [
                [
                    "product" => self::PRODUCTS_URL . "1",
                    "quantity" => 1
                ],
                [
                    "product" => self::PRODUCTS_URL . "2",
                    "quantity" => 2
                ]
            ]
        ];

        static::createClient()->request('PUT', self::CARTS_URL . '/1', [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => $data,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var Cart $cart */
        $cart = $this->cartRepository->find(1);
        $items = $cart->getItems();

        $this->assertCount(2, $items);

        $expectedItems = [
            ['name' => 'Product 1', 'price' => 10, 'quantity' => 1],
            ['name' => 'Product 2', 'price' => 22.2, 'quantity' => 2],
        ];

        foreach ($items as $index => $item) {
            /** @var Product $product */
            $product = $item->getProduct();
            $this->assertSame($expectedItems[$index]['name'], $product->getName());
            $this->assertEquals($expectedItems[$index]['price'], $item->getPrice());
            $this->assertSame($expectedItems[$index]['quantity'], $item->getQuantity());
        }
    }

    public function testPutNotFound(): void
    {
        $token = $this->login(self::ADMIN_MAIL, self::PLAIN_PASSWORD);

        $response = static::createClient()->request('PUT', self::CARTS_URL . '/1', [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
            'json' => [
                "product" => self::PRODUCTS_URL . 1,
                "quantity" => 1,
            ]
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}
