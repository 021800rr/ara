<?php

namespace App\EventListener;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class JWTAuthenticatedListener
{
    public function __construct(
        private RequestStack           $requestStack,
        private CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function onJWTAuthenticated(): void
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $authorizationHeader = (string) $request->headers->get('Authorization');
        $route = $request->attributes->get('_route');

        if (empty($authorizationHeader)) {
            return;
        }

        if ($token = $this->parse($authorizationHeader)) {
            $accessTokenOnBlackListItem = $this->cache->hasItem($token);
            if (('api_login_check' !== $route) && $accessTokenOnBlackListItem) {
                throw new HttpException(
                    Response::HTTP_UNAUTHORIZED,
                    'JWT Token not found',
                    null,
                    [],
                    401
                );
            }
        }
    }

    private function parse(string $authorizationHeader): false|string
    {
        $subString = 'Bearer ';

        return str_starts_with($authorizationHeader, $subString) ? substr($authorizationHeader, strlen($subString)) : false;
    }
}
