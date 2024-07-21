<?php

namespace App\Tests\Repository;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Tests\SetUpTrait;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpRepositories();
    }

    public function testUpgradePassword(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setLastName('Doe');
        $user->setEmail('test@example.com');
        $user->setPassword('old-password');

        $this->userRepository->save($user, true);

        $newHashedPassword = 'new-hashed-password';
        $this->userRepository->upgradePassword($user, $newHashedPassword);

        /** @var User $updatedUser */
        $updatedUser = $this->userRepository->find($user->getId());

        $this->assertSame($newHashedPassword, $updatedUser->getPassword());
    }

    public function testUpgradePasswordThrowsExceptionForUnsupportedUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
        $this->userRepository->upgradePassword($unsupportedUser, 'new-hashed-password');
    }
}
