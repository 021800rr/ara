<?php

namespace App\Dto;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final class TokenDto
{
    #[Assert\NotBlank]
    #[Assert\Type(type: Types::STRING)]
    public string $token;
}
