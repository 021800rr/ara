<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @implements ProcessorInterface<Cart, Cart>
 */
final readonly class CartProcessor implements ProcessorInterface
{
    public function __construct(
        private CartRepository $cartRepository,
        private Security       $security
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Cart
    {
        $cart = null;
        $user = $this->getUser();
        if ($operation instanceof Post) {
            $cart = new Cart();
            /** @var User $user */
            $cart->setUser($user);
        }
        $this->isCart($cart);
        /** @var Cart $cart */
        $this->cartRepository->save($cart, true);

        return $cart;
    }

    private function getUser(): UserInterface
    {
        // Retrieve the currently logged in user
        if (!$user = $this->security->getUser()) {
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                'User must be logged in to create a cart.',
                null,
                [],
                401
            );
        }

        return $user;
    }

    private function isCart(null|Cart $cart): void
    {
        if (!$cart) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'The system did not create a cart.',
                null,
                [],
                500
            );
        }
    }
}
