<?php

namespace App\Tests\ApiResource;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Tests\SetUpTrait;

class LogoutTest extends ApiTestCase
{
    use SetUpTrait;

    private const string LOGOUT_URL = '/api/logout';
    private const string LOGIN_EMAIL = 'not_exist@example.com';
    private const string LOGIN_PASSWORD = 'plain';

    private string $token;

    protected function setUp(): void
    {
        $this->setUpRepositories();
        $this->createUser(self::LOGIN_EMAIL, self::LOGIN_PASSWORD, User::ROLE_USER);
        $this->token = $this->login(self::LOGIN_EMAIL, self::LOGIN_PASSWORD);
    }

    public function testIncorrectBearerToken(): void
    {
        self::createClient()->request('POST', self::LOGOUT_URL, [
            'auth_bearer' => 'x',
            'headers' => self::HEADERS,
            'json' => ['token' => $this->token],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testNonExistentToken(): void
    {
        self::createClient()->request('POST', self::LOGOUT_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => 'x'],
        ]);
        $this->assertJsonContains([
            self::HYDRA_DESCRIPTION => 'Cannot get username from access token',
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testTokenIsNull(): void
    {
        self::createClient()->request('POST', self::LOGOUT_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => null],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testMissingToken(): void
    {
        self::createClient()->request('POST', self::LOGOUT_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => ""],
        ]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([self::HYDRA_DESCRIPTION => 'token: Ta wartość nie powinna być pusta.']);
    }

    public function testInvalidateTokens(): void
    {
        self::createClient()->request('GET', self::PRODUCTS_URL, [
            'auth_bearer' => $this->token,
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertSame(1, $this->refreshTokenRepository->count());
        self::createClient()->request('POST', self::LOGOUT_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => $this->token],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame(0, $this->refreshTokenRepository->count());

        // the token is blacklisted, no queries work
        self::createClient()->request('GET', self::PRODUCTS_URL, [
            'auth_bearer' => $this->token,
        ]);

        $this->assertJsonContains([self::HYDRA_DESCRIPTION => 'JWT Token not found']);
        $this->assertResponseStatusCodeSame(401);
    }
}
