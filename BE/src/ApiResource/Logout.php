<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\TokenDto;
use App\State\LogoutProcessor;

#[ApiResource]
#[Post(
    uriTemplate: '/logout.{_format}',
    input: TokenDto::class,
    processor: LogoutProcessor::class
)]
class Logout
{
}
