<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use App\State\CartProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartProcessorTest extends TestCase
{
    private CartProcessor $cartProcessor;

    /** @var CartRepository&MockObject $cartRepository */
    private CartRepository $cartRepository;

    /** @var Security&MockObject $security */
    private Security $security;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepository::class);
        $this->security = $this->createMock(Security::class);
        $this->cartProcessor = new CartProcessor($this->cartRepository, $this->security);
    }

    public function testProcessCreatesCart(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $this->cartRepository->expects($this->once())->method('save');

        $operation = new Post();
        $cart = $this->cartProcessor->process(new Cart(), $operation);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertSame($user, $cart->getUser());
    }

    public function testProcessThrowsExceptionWhenUserNotLoggedIn(): void
    {
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('User must be logged in to create a cart.');

        $operation = new Post();
        // @phpstan-ignore-next-line
        $this->cartProcessor->process(null, $operation);
    }

    public function testProcessThrowsExceptionWhenCartNotCreated(): void
    {
        $user = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn($user);

        $operation = $this->createMock(Operation::class);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The system did not create a cart.');

        // @phpstan-ignore-next-line
        $this->cartProcessor->process(null, $operation);
    }
}
