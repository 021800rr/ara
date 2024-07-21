<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_tokens')]
#[ApiResource(
    operations: [
        new Post(uriTemplate: '/token/refresh'),
    ]
)]
class RefreshToken extends BaseRefreshToken
{
    /** {@inheritDoc} */
    #[SerializedName('refresh_token')]
    protected $refreshToken;

    /** {@inheritDoc} */
    #[Ignore]
    protected $username;

    /** {@inheritDoc} */
    #[Ignore]
    protected $valid;
}
