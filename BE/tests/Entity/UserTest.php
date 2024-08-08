<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Config\UserStatus;
use App\Entity\User;
use App\Tests\SetUpTrait;

class UserTest extends ApiTestCase
{
    use SetUpTrait;

    private string $token;

    protected function setUp(): void
    {
        $this->token = $this->login(self::ADMIN_MAIL, self::PLAIN_PASSWORD);
    }

    public function testGet(): void
    {
        self::createClient()->request('GET', self::USERS_URL . '/' . self::EDITOR_ID, [
            'auth_bearer' => $this->token
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => 2,
            'name' => 'Ignacy2',
            'lastName' => 'Rzecki2',
            'email' => 'editor@example.com',
            'roles' => [
                User::ROLE_EDITOR,
            ],
            'status' => UserStatus::active->name,
        ]);
    }

    public function testGetCollection(): void
    {
        self::createClient()->request('GET', self::USERS_URL, ['auth_bearer' => $this->token]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            self::HYDRA_TOTAL_ITEMS => 3,
            self::HYDRA_MEMBER => [
                [
                    'id' => 1,
                    'name' => 'Jakub1',
                    'lastName' => 'Lange1',
                    'email' => 'admin@example.com',
                    'roles' => [
                        User::ROLE_ADMIN,
                    ],
                    'status' => UserStatus::active->name,
                ],
                [
                    'id' => 2,
                    'name' => 'Ignacy2',
                    'lastName' => 'Rzecki2',
                    'email' => 'editor@example.com',
                    'roles' => [
                        User::ROLE_EDITOR,
                    ],
                    'status' => UserStatus::active->name,
                ],
                [
                    'id' => 3,
                    'name' => 'Julian3',
                    'lastName' => 'Ochocki3',
                    'email' => 'user@example.com',
                    'roles' => [
                        User::ROLE_USER,
                    ],
                    'status' => UserStatus::active->name,
                ],
            ]
        ]);
    }

    public function testGetCollectionByNameAndSurname(): void
    {
        $client = self::createClient();
        $client->getKernelBrowser()->followRedirects(true);
        $client->request('GET', self::USERS_URL . '/?name=i&lastName=cki', ['auth_bearer' => $this->token]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            self::HYDRA_TOTAL_ITEMS => 2,
            self::HYDRA_MEMBER => [
                [
                    'name' => 'Ignacy2',
                    'lastName' => 'Rzecki2',
                ],
                [
                    'name' => 'Julian3',
                    'lastName' => 'Ochocki3',
                ]
            ],
        ]);
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('john.doe@example.com');

        $this->assertSame('john.doe@example.com', $user->getUserIdentifier());
    }
}
