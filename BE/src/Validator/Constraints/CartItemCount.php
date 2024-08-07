<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CartItemCount extends Constraint
{
    public string $message = 'You cannot add more than {{ limit }} items to the cart.';
}
