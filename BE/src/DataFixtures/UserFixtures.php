<?php

namespace App\DataFixtures;

use App\Config\UserStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private const DEFAULT_PASSWORD = 'test';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Loads a set of test users into the database.
     *
     * @param ObjectManager $manager The ObjectManager instance
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $this->createUser(
            $manager,
            'Jakub1',
            'Lange1',
            'admin@example.com',
            [User::ROLE_ADMIN]
        );

        $this->createUser(
            $manager,
            'Ignacy2',
            'Rzecki2',
            'editor@example.com',
            [User::ROLE_EDITOR]
        );

        $this->createUser(
            $manager,
            'Julian3',
            'Ochocki3',
            'user@example.com',
            [User::ROLE_USER]
        );

        $manager->flush();
    }

    /**
     * Creates a user and persists it in the database.
     *
     * @param ObjectManager $manager The ObjectManager instance
     * @param string $name The first name of the user
     * @param string $lastName The last name of the user
     * @param string $email The email address of the user
     * @param array<string> $roles The roles assigned to the user
     * @return void
     */
    private function createUser(
        ObjectManager $manager,
        string $name,
        string $lastName,
        string $email,
        array $roles
    ): void {
        $user = new User();
        $user->setName($name);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setStatus(UserStatus::active->name);
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            self::DEFAULT_PASSWORD
        ));
        $manager->persist($user);
    }
}
