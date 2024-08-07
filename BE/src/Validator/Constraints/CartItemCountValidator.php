<?php

namespace App\Validator\Constraints;

use App\Entity\Cart;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CartItemCountValidator extends ConstraintValidator
{
    public function __construct(#[Autowire('%cart_item_limit%')] private readonly int $cartItemLimit)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CartItemCount) {
            throw new UnexpectedTypeException($constraint, CartItemCount::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Cart) {
            throw new UnexpectedValueException($value, Cart::class);
        }

        $totalItems = 0;
        foreach ($value->getItems() as $item) {
            $totalItems += $item->getQuantity();
        }

        if ($totalItems > $this->cartItemLimit) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ limit }}', (string) $this->cartItemLimit)
                ->addViolation();
        }
    }
}
