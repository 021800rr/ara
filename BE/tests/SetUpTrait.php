<?php

namespace App\Tests;

use App\Config\UserStatus;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait SetUpTrait
{
    private UserRepository $userRepository;
    private RefreshTokenRepository $refreshTokenRepository;
    private ProductRepository $productRepository;
    private ValidatorInterface $validator;

    private function createUser(string $email, string $password, string $role): void
    {
        $user = new User();

        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $userPasswordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $userPasswordHasher->hashPassword($user, $password);

        $user->setEmail($email);
        $user->setRoles([$role]);
        $user->setPassword($hashedPassword);
        $user->setName('x');
        $user->setLastName('x');
        $user->setStatus(UserStatus::active->name);

        $this->userRepository->save($user, true);
    }

    private function login(string $username, string $password): string
    {
        $response = self::createClient()->request('POST', '/api/login/check', [
            'json' => [
                'username' => $username,
                'password' => $password
            ],
        ]);

        return $response->toArray()['token'];
    }

    private function setUpRepositories(): void
    {
        $this->userRepository = $this->getUserRepository();
        $this->refreshTokenRepository = $this->getRefreshTokenRepository();
        $this->productRepository = $this->getProductRepository();
    }

    private function setUpValidator(): void
    {
        /** @var ValidatorInterface $validator */
        $validator = static::getContainer()->get(ValidatorInterface::class);
        $this->validator = $validator;
    }

    private function getUserRepository(): UserRepository
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        return $userRepository;
    }

    private function getRefreshTokenRepository(): RefreshTokenRepository
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();

        /** @var RefreshTokenRepository $repository */
        $repository = $entityManager->getRepository(RefreshToken::class);

        return $repository;
    }

    private function getProductRepository(): ProductRepository
    {
        /** @var ProductRepository $productRepository */
        $productRepository = static::getContainer()->get(ProductRepository::class);

        return $productRepository;
    }
}
