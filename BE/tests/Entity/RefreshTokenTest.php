<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Tests\SetUpTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RefreshTokenTest extends ApiTestCase
{
    use SetUpTrait;

    private const string LOGIN_URL = '/api/login/check';
    private const string REFRESH_URL = '/api/token/refresh';

    public function testRefreshToken(): void
    {
        $tokens = $this->loginAndGetTokens(self::ADMIN_MAIL, self::PLAIN_PASSWORD);
        $response = $this->refreshToken($tokens['refresh_token']);
        $this->assertResponseIsSuccessful();

        /** @var array<string, string> $newTokens */
        $newTokens = json_decode($response->getContent(), true);
        $this->assertNotNull($newTokens, 'Failed to decode JSON response');

        $this->assertIsArray($newTokens);
        $this->assertArrayHasKey('refresh_token', $newTokens);
        $this->assertArrayHasKey('token', $newTokens);
        $this->assertSame($tokens['refresh_token'], $newTokens['refresh_token']);

        /** @var object{
         *      username: string,
         *      roles: string[]
         * } $jwtPayload
         */
        $jwtPayload = $this->decodeJwt($newTokens['token']);

        $this->assertSame(self::ADMIN_MAIL, $jwtPayload->username);
        $this->assertSame([User::ROLE_ADMIN, User::ROLE_USER], $jwtPayload->roles);
    }

    /**
     * @return array<string, string>
     */
    private function loginAndGetTokens(string $username, string $password): array
    {
        $response = self::createClient()->request(
            'POST',
            self::LOGIN_URL,
            [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]
        );
        $this->assertResponseIsSuccessful();

        $tokens = $response->toArray();
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertArrayHasKey('token', $tokens);

        return $tokens;
    }

    private function refreshToken(string $refreshToken): ResponseInterface
    {
        return self::createClient()->request(
            'POST',
            self::REFRESH_URL,
            [
                'headers' => self::HEADERS,
                'json' => [
                    'refresh_token' => $refreshToken,
                ],
            ]
        );
    }

    private function decodeJwt(string $jwt): object
    {
        $parts = explode(".", $jwt);
        $this->assertCount(3, $parts);

        $json = base64_decode($parts[1]);
        $this->assertNotFalse($json, 'Failed to decode base64 JWT payload');

        /** @var object{
         *      username: string,
         *      roles: string[]
         * } $jwtPayload
         */
        $jwtPayload = json_decode($json);
        $this->assertIsObject($jwtPayload, 'Failed to decode JSON payload from JWT');

        return $jwtPayload;
    }
}
