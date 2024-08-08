<?php

namespace App\Tests\EventListener;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Config\UserStatus;
use App\Entity\User;
use App\EventListener\CheckUserStatusListener;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CheckUserStatusListenerTest extends ApiTestCase
{
    /** @var Security&MockObject */
    private Security $security;

    protected function setUp(): void
    {
        /** @var Security&MockObject $securityMock */
        $securityMock = $this->createMock(Security::class);
        $this->security = $securityMock;
    }

    private function createRequestEvent(User $user = null): RequestEvent
    {
        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);

        $this->security
            ->method('getUser')
            ->willReturn($user);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    public function testOnKernelRequestWithActiveUser(): void
    {
        $user = new User();
        $user->setStatus(UserStatus::active->name);

        $event = $this->createRequestEvent($user);
        $listener = new CheckUserStatusListener($this->security);

        $listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testOnKernelRequestWithInactiveUser(): void
    {
        $user = new User();
        $user->setStatus(UserStatus::inactive->name);

        $event = $this->createRequestEvent($user);
        $listener = new CheckUserStatusListener($this->security);

        $listener->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(
            ['message' => 'User status is not active'],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testOnKernelRequestWithNoUser(): void
    {
        $event = $this->createRequestEvent();
        $listener = new CheckUserStatusListener($this->security);

        $listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }
}
