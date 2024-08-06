<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Tests\SetUpTrait;
use DateTime;

class CartTest extends ApiTestCase
{
    use SetUpTrait;

    private const array HEADERS = ['Content-Type' => 'application/ld+json'];
    private const string USER_MAIL = 'user@example.com';
    private const string PLAIN_PASSWORD = 'test';
    private const int USER_ID = 3;
    private const string CARTS_URL = '/api/carts';
    private const string USERS_URL = '/api/users/';

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

    public function testGetCollection(): void
    {
        $token = $this->login(self::USER_MAIL, self::PLAIN_PASSWORD);

        for ($i = 0; $i < 3; $i++) {
            static::createClient()->request('POST', self::CARTS_URL, [
                'auth_bearer' => $token,
                'headers' => self::HEADERS,
                'json' => []
            ]);

            $this->assertResponseStatusCodeSame(201);
        }

        $response = static::createClient()->request('GET', self::CARTS_URL, [
            'auth_bearer' => $token,
            'headers' => self::HEADERS,
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var array<"hydra:member", array<int, mixed>> $carts */
        $carts = json_decode($response->getContent(), true);
        $this->assertCount(4, $carts['hydra:member']);
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
}
