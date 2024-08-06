<?php

namespace App\DataFixtures;

use App\Config\UserStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const string USER = 'user';
    private const string DEFAULT_PASSWORD = 'test';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setName('Jakub1');
        $admin->setLastName('Lange1');
        $admin->setEmail('admin@example.com');
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setStatus(UserStatus::active->name);
        $admin->setPassword($this->passwordHasher->hashPassword(
            $admin,
            self::DEFAULT_PASSWORD
        ));

        $manager->persist($admin);

        $editor = new User();
        $editor->setName('Ignacy2');
        $editor->setLastName('Rzecki2');
        $editor->setEmail('editor@example.com');
        $editor->setRoles([User::ROLE_EDITOR]);
        $editor->setStatus(UserStatus::active->name);
        $editor->setPassword($this->passwordHasher->hashPassword(
            $editor,
            self::DEFAULT_PASSWORD
        ));

        $manager->persist($editor);

        $user = new User();
        $user->setName('Julian3');
        $user->setLastName('Ochocki3');
        $user->setEmail('user@example.com');
        $user->setRoles([User::ROLE_USER]);
        $user->setStatus(UserStatus::active->name);
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            self::DEFAULT_PASSWORD
        ));

        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::USER, $user);
    }
}
