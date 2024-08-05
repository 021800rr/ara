<?php

namespace App\Dto;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDto
{
    #[Assert\NotBlank]
    #[Assert\Type(type: Types::STRING)]
    #[Groups(['product:write'])]
    public string $name;

    #[Assert\Type(type: Types::STRING)]
    #[Groups(['product:write'])]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    #[Groups(['product:write'])]
    public null|float|int $price;
}
