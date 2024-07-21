<?php

namespace App\Tests\Dto;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\TokenDto;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TokenDtoTest extends ApiTestCase
{
    private ValidatorInterface $validator;

    /**
     * Boot the Symfony kernel and get the validator service.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ValidatorInterface $validator */
        $validator = static::getContainer()->get(ValidatorInterface::class);
        $this->validator = $validator;
    }

    #[DataProvider('tokenProvider')]
    public function testTokenValidation(string $token, int $expectedViolationCount, ?string $expectedMessage): void
    {
        $dto = new TokenDto();
        $dto->token = $token;

        $violations = $this->validator->validate($dto);

        $this->assertCount($expectedViolationCount, $violations);

        if ($expectedViolationCount > 0) {
            /** @var ConstraintViolation $constraintViolation */
            $constraintViolation = $violations[0];
            $this->assertSame($expectedMessage, $constraintViolation->getMessage());
        }
    }

    /**
     * @return array<string, array{string, int, null|string}>
     */
    public static function tokenProvider(): array
    {
        return [
            'valid token' => ['some-token', 0, null],
            'empty token' => ['', 1, 'Ta wartość nie powinna być pusta.'],
        ];
    }
}
