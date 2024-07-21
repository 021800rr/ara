<?php

namespace App\Dto;

use App\Validator\Constraints as AppAssert;
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

    /**
     * @var array<string, float>
     */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('numeric'),
        new Assert\NotBlank(),
    ])]
    #[AppAssert\ValidCurrency]
    #[Groups(['product:write'])]
    public array $prices = [];
}
