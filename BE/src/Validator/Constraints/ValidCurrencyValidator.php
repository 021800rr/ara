<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidCurrencyValidator extends ConstraintValidator
{
    /**
     * @param string[] $availableCurrencies
     */
    public function __construct(#[Autowire('%available_currencies%')] private readonly array $availableCurrencies)
    {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidCurrency) {
            throw new UnexpectedTypeException($constraint, ValidCurrency::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        foreach (array_keys($value) as $currency) {
            if (!in_array($currency, $this->availableCurrencies, true)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ currency }}', $currency)
                    ->addViolation();
            }
        }
    }
}
