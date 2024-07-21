<?php

namespace App\Tests\Dto;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\TokenDto;
use App\Tests\SetUpTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\ConstraintViolation;

class TokenDtoTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpValidator();
    }

    #[DataProvider('tokenProvider')]
    public function testTokenDto(string $token, int $expectedViolationCount, ?string $expectedMessage): void
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
