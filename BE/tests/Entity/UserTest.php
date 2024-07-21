<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Config\UserStatus;
use App\Entity\User;
use App\Tests\SetupTrait;

class   UserTest extends ApiTestCase
{
    use SetupTrait;

    private const USER_URL = '/api/users';
    private const HYDRA_TOTAL_ITEMS = 'hydra:totalItems';
    private const HYDRA_MEMBER = 'hydra:member';

    private const LOGIN_EMAIL = 'admin@example.com';
    private const LOGIN_PASSWORD = 'test';

    protected function setUp(): void
    {
        $this->setRepositories();
    }

    public function testGet(): void
    {
        $token = $this->login(self::LOGIN_EMAIL, self::LOGIN_PASSWORD);
        self::createClient()->request(
            'GET',
            self::USER_URL . '/2',
            ['auth_bearer' => $token]
        );
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
        $token = $this->login(self::LOGIN_EMAIL, self::LOGIN_PASSWORD);

        self::createClient()->request('GET', self::USER_URL, ['auth_bearer' => $token]);
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

    public function testGetCollectionByPartOfNameAndSurnameCaseInsensitive(): void
    {
        $token = $this->login(self::LOGIN_EMAIL, self::LOGIN_PASSWORD);
        $client = self::createClient();
        $client->getKernelBrowser()->followRedirects(true);
        $client->request('GET', self::USER_URL . '/?name=i&lastName=cki', ['auth_bearer' => $token]);
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

    public function testDefaultRoleIncludesRoleUser(): void
    {
        $user = new User();
        $roles = $user->getRoles();

        $this->assertContains(User::ROLE_USER, $roles);
    }

    public function testSetRolesAddsRoleUser(): void
    {
        $user = new User();
        $roles = [User::ROLE_ADMIN];
        $user->setRoles($roles);

        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
        $this->assertContains(User::ROLE_USER, $user->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $user = new User();
        $roles = [User::ROLE_ADMIN, User::ROLE_USER, User::ROLE_USER];
        $user->setRoles($roles);

        $this->assertSame([User::ROLE_ADMIN, User::ROLE_USER], $user->getRoles());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('john.doe@example.com');

        $this->assertSame('john.doe@example.com', $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();

        // This method doesn't do anything currently, but we can still call it to ensure it doesn't throw an error.
        $this->assertTrue(true);
    }

    public function testStatusDefaultsToInactive(): void
    {
        $user = new User();

        $this->assertSame(UserStatus::inactive->name, $user->getStatus());
    }
}
