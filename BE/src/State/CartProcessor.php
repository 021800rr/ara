<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\CartRepository;
use DateTime;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements ProcessorInterface<Cart, Cart>
 */
final readonly class CartProcessor implements ProcessorInterface
{
    public function __construct(
        private CartRepository     $cartRepository,
        private Security           $security,
        private ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Cart
    {
        if (!$data instanceof Cart) {
            throw new UnexpectedTypeException($data, Cart::class);
        }
        /** @var User $user */
        $user = $this->getUser();

        if ($operation instanceof Post) {
            $cart = new Cart();
            $cart->setUser($user);

            $this->addItemsToCart($data, $cart);
        } elseif ($operation instanceof Put) {
            /** @var Cart $cart */
            $cart = $this->cartRepository->findOneBy([
                'id' => $uriVariables['id'],
                'user' => $user,
            ]);
            foreach ($cart->getItems() as $item) {
                $cart->removeItem($item);
            }
            $this->addItemsToCart($data, $cart);
            $cart->setUpdatedAt(new DateTime());
        } else {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'Unsupported operation.'
            );
        }

        /** @var Cart $cart */
        $this->validateCartItems($cart);
        $this->cartRepository->save($cart, true);

        return $cart;
    }

    private function getUser(): UserInterface
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
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

    private function validateCartItems(Cart $cart): void
    {
        try {
            foreach ($cart->getItems() as $item) {
                $violations = $this->validator->validate($item);
                if (count($violations) > 0) {
                    $errors = [];
                    foreach ($violations as $violation) {
                        $errors[] = $violation->getMessage();
                    }
                    throw new HttpException(
                        Response::HTTP_BAD_REQUEST,
                        implode(', ', $errors)
                    );
                }
            }
        } catch (UnexpectedTypeException | UnexpectedValueException $e) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'Validation error occurred: ' . $e->getMessage(),
                $e
            );
        } catch (HttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An unexpected error occurred during validation.',
                $e
            );
        }
    }

    private function addItemsToCart(Cart $data, Cart $cart): void
    {
        foreach ($data->getItems() as $item) {
            $cartItem = new CartItem();
            if ($product = $item->getProduct()) {

                $cartItem->setProduct($product);
                $cartItem->setQuantity($item->getQuantity());
                $cartItem->setPrice($product->getPrice());

                $cart->addItem($cartItem);
            }
        }
    }
}
