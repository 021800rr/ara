<?php

namespace App\Tests\ApiResource;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Config\UserStatus;
use App\Entity\User;
use App\Tests\SetupTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LogoutTest extends ApiTestCase
{
    use SetupTrait;

    private const string API_URL = '/api/logout';
    private const array HEADERS = ['Content-Type' => 'application/ld+json'];

    private string $token;

    protected function setUp(): void
    {
        $this->setRepositories();
        $this->createUser();
        $this->token = $this->login('not_exist@example.com', 'plain');
    }

    /**
     * Test that an incorrect bearer token results in a 401 Unauthorized response.
     */
    public function testIncorrectBearerToken(): void
    {
        self::createClient()->request('POST', self::API_URL, [
            'auth_bearer' => 'x',
            'headers' => self::HEADERS,
            'json' => ['token', $this->token],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test that using a non-existent token in the DTO results in a 400 Bad Request response
     * with an appropriate error message.
     */
    public function testNonExistentToken(): void
    {
        self::createClient()->request('POST', self::API_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => 'x'],
        ]);
        $this->assertJsonContains([
            'hydra:description' => 'Cannot get username from access token',
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Test that providing a null token in the DTO results in a 400 Bad Request response.
     */
    public function testTokenIsNull(): void
    {
        self::createClient()->request('POST', self::API_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => null],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Test that providing an empty string as a token in the DTO results in a 500 Internal Server Error
     * with an appropriate error message.
     */
    public function testMissingToken(): void
    {
        self::createClient()->request('POST', self::API_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => ""],
        ]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['hydra:description' => 'token: Ta wartość nie powinna być pusta.']);
    }

    /**
     * Test that logging out invalidates the token, resulting in a 201 "Created" response,
     * and subsequent requests using the invalidated token result in a 401 Unauthorized response.
     */
    public function testInvalidateTokens(): void
    {
        $this->assertSame(1, $this->refreshTokenRepository->count());
        self::createClient()->request('POST', self::API_URL, [
            'auth_bearer' => $this->token,
            'headers' => self::HEADERS,
            'json' => ['token' => $this->token],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame(0, $this->refreshTokenRepository->count());

        // the token is blacklisted or no longer valid, no queries work
        self::createClient()->request(
            'GET',
            '/api/users/1',
            ['auth_bearer' => $this->token]
        );
        $this->assertJsonContains(['hydra:description' => 'JWT Token not found',]);
        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Create a user for testing purposes and save it to the repository.
     */
    private function createUser(): void
    {
        $user = new User();

        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $userPasswordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $userPasswordHasher->hashPassword($user, 'plain');

        $user->setEmail('not_exist@example.com');
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setPassword($hashedPassword);
        $user->setName('x');
        $user->setLastName('x');
        $user->setStatus(UserStatus::active->name);

        $this->userRepository->save($user, true);
    }
}
