<?php

namespace App\Tests\EventListener;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\EventListener\JWTAuthenticatedListener;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class JWTAuthenticatedListenerTest extends ApiTestCase
{
    /** @var CacheItemPoolInterface&MockObject */
    private CacheItemPoolInterface $cache;
    private RequestStack $requestStack;
    private JWTAuthenticatedListener $listener;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        /** @var CacheItemPoolInterface&MockObject $cacheMock */
        $cacheMock = $this->createMock(CacheItemPoolInterface::class);
        $this->cache = $cacheMock;
        $this->listener = new JWTAuthenticatedListener($this->requestStack, $this->cache);
    }

    public function testOnJWTAuthenticatedWithoutAuthorizationHeader(): void
    {
        $event = $this->createRequestEvent();
        $this->requestStack->push($event->getRequest());
        $this->listener->onJWTAuthenticated();

        $this->assertNull($event->getResponse());
    }

    public function testOnJWTAuthenticatedWithValidToken(): void
    {
        $event = $this->createRequestEvent('some_route', 'Bearer valid-token');
        $this->requestStack->push($event->getRequest());
        $this->cache->method('hasItem')->willReturn(false);
        $this->listener->onJWTAuthenticated();

        $this->assertNull($event->getResponse());
    }

    public function testOnJWTAuthenticatedWithBlacklistedToken(): void
    {
        $event = $this->createRequestEvent('some_route', 'Bearer blacklisted-token');
        $this->requestStack->push($event->getRequest());
        $this->cache->method('hasItem')->willReturn(true);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('JWT Token not found');

        $this->listener->onJWTAuthenticated();
    }

    public function testOnJWTAuthenticatedWithBlacklistedTokenOnLoginRoute(): void
    {
        $event = $this->createRequestEvent('api_login_check', 'Bearer blacklisted-token');
        $this->requestStack->push($event->getRequest());

        $this->cache->method('hasItem')->willReturn(false);

        $this->listener->onJWTAuthenticated();

        $this->assertNull($event->getResponse());
    }

    public function testOnJWTAuthenticatedWithoutAuthorizationHeaderOnLoginRoute(): void
    {
        $event = $this->createRequestEvent('api_login_check');
        $this->requestStack->push($event->getRequest());

        $this->cache->method('hasItem')->willReturn(false);

        $this->listener->onJWTAuthenticated();

        $this->assertNull($event->getResponse());
    }

    private function createRequestEvent(string $route = 'some_route', string $authorizationHeader = null): RequestEvent
    {
        $request = new Request(
            [],
            [],
            ['_route' => $route],
            [],
            [],
            $authorizationHeader ? ['HTTP_AUTHORIZATION' => $authorizationHeader] : []
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
