<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use App\State\CartProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CartProcessorTest extends TestCase
{
    private CartProcessor $cartProcessor;

    /** @var CartRepository&MockObject */
    private CartRepository $cartRepository;

    /** @var Security&MockObject */
    private Security $security;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepository::class);
        $this->security = $this->createMock(Security::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $this->cartProcessor = new CartProcessor($this->cartRepository, $this->security, $validator);
    }

    public function testProcessCreatesCart(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $this->cartRepository->expects($this->once())->method('save');

        $cart = $this->cartProcessor->process(new Cart(), new Post());

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertSame($user, $cart->getUser());
    }

    public function testProcessThrowsExceptionWhenUserNotLoggedIn(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('User must be logged in to create a cart.');

        $this->cartProcessor->process(new Cart(), new Post());
    }

    public function testProcessUpdatesCart(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $existingCart = new Cart();
        $existingCart->setUser($user);

        $this->cartRepository->method('findOneBy')->willReturn($existingCart);
        $this->cartRepository->expects($this->once())->method('save');

        $updatedCart = $this->cartProcessor->process(new Cart(), new Put(), ['id' => 1]);

        $this->assertInstanceOf(Cart::class, $updatedCart);
        $this->assertSame($user, $updatedCart->getUser());
        $this->assertCount(0, $updatedCart->getItems());
    }
}
