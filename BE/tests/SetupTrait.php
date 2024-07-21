<?php

namespace App\Tests;

use App\Entity\RefreshToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;

trait SetupTrait
{
    private UserRepository $userRepository;
    private RefreshTokenRepository $refreshTokenRepository;

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

    private function setRepositories(): void
    {
        $this->userRepository = $this->getUserRepository();
        $this->refreshTokenRepository = $this->getRefreshTokenRepository();
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
        $doctrine = self::bootKernel()->getContainer()->get('doctrine');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();

        /** @var RefreshTokenRepository $repository */
        $repository = $entityManager->getRepository(RefreshToken::class);

        return $repository;
    }
}
