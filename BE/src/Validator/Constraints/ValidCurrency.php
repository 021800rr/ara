<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidCurrency extends Constraint
{
    public string $message = 'The currency "{{ currency }}" is not supported.';
}
