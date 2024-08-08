<?php

namespace App\Tests;

use App\Config\UserStatus;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\State\ItemProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait SetUpTrait
{
    private const array HEADERS = ['Content-Type' => 'application/ld+json'];
    private const string HYDRA_MEMBER = 'hydra:member';
    private const string HYDRA_DESCRIPTION = 'hydra:description';
    private const string HYDRA_TOTAL_ITEMS = 'hydra:totalItems';

    private const string USERS_URL = '/api/users';
    private const string ADMIN_MAIL = 'admin@example.com';
    private const string EDITOR_MAIL = 'editor@example.com';
    private const string USER_MAIL = 'user@example.com';
    private const string PLAIN_PASSWORD = 'test';
    private const int EDITOR_ID = 2;
    private const int USER_ID = 3;

    private const string CARTS_URL = '/api/carts';
    private const int CART_ID = 1;

    private const string ITEMS_URL = '/api/items';
    private const int CART_ITEM_ID = 1;
    private const int NON_EXISTING_CART_ITEM_ID = 999;

    private const string PRODUCTS_URL = '/api/products';
    private const int PRODUCT_ID_1 = 1;
    private const int PRODUCT_ID_2 = 2;

    private UserRepository $userRepository;
    private RefreshTokenRepository $refreshTokenRepository;
    private ProductRepository $productRepository;
    private Product $product;
    private CartRepository $cartRepository;
    private Cart $cart;
    private CartItemRepository $cartItemRepository;
    private CartItem $cartItem;
    private ValidatorInterface $validator;
    private ItemProcessor $itemProcessor;

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
        $this->cartRepository = $this->getCartRepository();
        $this->cartItemRepository = $this->getCartItemRepository();
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

    private function getCartRepository(): CartRepository
    {
        /** @var CartRepository $cartRepository */
        $cartRepository = static::getContainer()->get(CartRepository::class);

        return $cartRepository;
    }

    private function getCartItemRepository(): CartItemRepository
    {
        /** @var CartItemRepository $cartItemRepository */
        $cartItemRepository = static::getContainer()->get(CartItemRepository::class);

        return $cartItemRepository;
    }
}
