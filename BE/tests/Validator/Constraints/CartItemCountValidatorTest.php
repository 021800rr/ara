<?php

namespace App\Tests\Validator\Constraints;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Validator\Constraints\CartItemCount;
use App\Validator\Constraints\CartItemCountValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<CartItemCountValidator>
 */
class CartItemCountValidatorTest extends ConstraintValidatorTestCase
{
    private const CART_ITEM_LIMIT = 5;

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new CartItemCountValidator(self::CART_ITEM_LIMIT);
    }

    public function testValidCartItemCount(): void
    {
        $cart = new Cart();
        $cartItem1 = new CartItem();
        $cartItem1->setQuantity(2);
        $cartItem2 = new CartItem();
        $cartItem2->setQuantity(3);

        $cart->addItem($cartItem1);
        $cart->addItem($cartItem2);

        $constraint = new CartItemCount();

        $this->validator->validate($cart, $constraint);

        $this->assertNoViolation();
    }

    public function testInvalidCartItemCount(): void
    {
        $cart = new Cart();
        $cartItem1 = new CartItem();
        $cartItem1->setQuantity(3);
        $cartItem2 = new CartItem();
        $cartItem2->setQuantity(4);

        $cart->addItem($cartItem1);
        $cart->addItem($cartItem2);

        $constraint = new CartItemCount(['message' => 'You cannot add more than {{ limit }} items to the cart.']);

        $this->validator->validate($cart, $constraint);

        $this->buildViolation('You cannot add more than {{ limit }} items to the cart.')
            ->setParameter('{{ limit }}', (string) self::CART_ITEM_LIMIT)
            ->assertRaised();
    }

    public function testNullValue(): void
    {
        $constraint = new CartItemCount();

        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testInvalidValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $constraint = new CartItemCount();

        $this->validator->validate('invalid_value', $constraint);
    }

    public function testUnexpectedType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $value = new Cart();
        $constraint = $this->createMock(Constraint::class);

        $this->validator->validate($value, $constraint);
    }
}
