<?php

namespace App\Tests\Validator\Constraints;

use App\Validator\Constraints\ValidCurrency;
use App\Validator\Constraints\ValidCurrencyValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ValidCurrencyValidator>
 */
class ValidCurrencyValidatorTest extends ConstraintValidatorTestCase
{
    /** @var string[] */
    private array $availableCurrencies = ['USD', 'EUR'];

    protected function createValidator(): ValidCurrencyValidator
    {
        return new ValidCurrencyValidator($this->availableCurrencies);
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new ValidCurrency());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new ValidCurrency());

        $this->assertNoViolation();
    }

    public function testValidCurrencies(): void
    {
        $this->validator->validate(['USD' => 10.1, 'EUR' => 20], new ValidCurrency());

        $this->assertNoViolation();
    }

    public function testInvalidCurrency(): void
    {
        $constraint = new ValidCurrency(['message' => 'The currency "{{ currency }}" is not supported.']);
        $this->validator->validate(['USD' => 10, 'VND' => 20000], $constraint);

        $this->buildViolation('The currency "{{ currency }}" is not supported.')
            ->setParameter('{{ currency }}', 'VND')
            ->assertRaised();
    }

    public function testInvalidTypeThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate('invalid_type', new ValidCurrency());
    }

    public function testInvalidConstraintThrowsUnexpectedTypeException(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(['USD' => 10], $this->createMock(Constraint::class));
    }
}
