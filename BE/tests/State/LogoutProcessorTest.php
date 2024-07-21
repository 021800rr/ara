<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use App\Dto\TokenDto;
use App\Entity\RefreshToken;
use App\State\LogoutProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LogoutProcessorTest extends TestCase
{
    /** @var CacheInterface&MockObject */
    private CacheInterface $cache;

    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    /** @var RefreshTokenManagerInterface&MockObject */
    private RefreshTokenManagerInterface $refreshTokenManager;

    private LogoutProcessor $logoutProcessor;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->refreshTokenManager = $this->createMock(RefreshTokenManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->logoutProcessor = new LogoutProcessor(
            $this->cache,
            3600, // JWT TTL
            $this->refreshTokenManager,
            $this->entityManager,
            $this->logger
        );
    }

    public function testProcessSuccessfullyBlacklistsToken(): void
    {
        $tokenDto = $this->getDto();
        $this->setUpCacheMock($tokenDto);
        $refreshToken = $this->setUpRefreshTokenMock();
        $repository = $this->setUpRepositoryMock($refreshToken);
        $this->setUpEntityManagerMock($repository);
        $this->setUpRefreshTokenManagerMock($refreshToken);

        // Setting a flag to track logger calls
        $loggerCalls = [];

        $this->logger->expects($this->exactly(3))
            ->method('debug')
            ->willReturnCallback(function ($message, $context) use (&$loggerCalls) {
                $loggerCalls[] = [$message, $context];
            });

        $operation = $this->createMock(Operation::class);

        $this->logoutProcessor->process($tokenDto, $operation);

        // Checking whether the logger was invoked in the correct order
        $this->assertCount(3, $loggerCalls);
        $this->assertSame('Adding token to blacklist', $loggerCalls[0][0]);
        $this->assertSame(['token' => $tokenDto->token], $loggerCalls[0][1]);

        // @phpstan-ignore-next-line
        $this->assertSame('Decoded JWT token', $loggerCalls[1][0]);
        // @phpstan-ignore-next-line
        $this->assertSame(['username' => 'test@example.com'], $loggerCalls[1][1]);

        // @phpstan-ignore-next-line
        $this->assertSame('Deleting refresh token', $loggerCalls[2][0]);
        // @phpstan-ignore-next-line
        $this->assertSame(['refresh_token' => 'some-refresh-token'], $loggerCalls[2][1]);
    }

    public function testProcessThrowsExceptionForInvalidToken(): void
    {
        $tokenDto = new TokenDto();
        $tokenDto->token = 'invalid.token';

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Cannot get username from access token');

        $operation = $this->createMock(Operation::class);

        $this->logoutProcessor->process($tokenDto, $operation);
    }

    private function createValidToken(): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode(['username' => 'test@example.com']);

        if ($header === false || $payload === false) {
            throw new \RuntimeException('JSON encoding failed');
        }

        $headerEncoded = base64_encode($header);
        $payloadEncoded = base64_encode($payload);

        if (!$headerEncoded || !$payloadEncoded) {
            throw new \RuntimeException('Base64 encoding failed');
        }

        $signature = 'dummy_signature';

        return sprintf('%s.%s.%s', $headerEncoded, $payloadEncoded, $signature);
    }

    private function getDto(): TokenDto
    {
        $tokenDto = new TokenDto();
        $tokenDto->token = $this->createValidToken();

        return $tokenDto;
    }

    private function setUpCacheMock(TokenDto $tokenDto): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->with($tokenDto->token)
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->createMock(ItemInterface::class));
            });
    }

    /**
     * @return RefreshToken&MockObject
     * @throws Exception
     */
    private function setUpRefreshTokenMock(): MockObject
    {
        $refreshToken = $this->createMock(RefreshToken::class);
        $refreshToken->expects($this->once())
            ->method('getRefreshToken')
            ->willReturn('some-refresh-token');

        return $refreshToken;
    }

    /**
     * @param MockObject&RefreshToken $refreshToken
     * @return EntityRepository<RefreshToken>&MockObject
     * @throws Exception
     */
    private function setUpRepositoryMock(MockObject $refreshToken): MockObject
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with(['username' => 'test@example.com'])
            ->willReturn([$refreshToken]);

        return $repository;
    }

    /**
     * @param EntityRepository<RefreshToken>&MockObject $repository
     */
    private function setUpEntityManagerMock(MockObject $repository): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(RefreshToken::class)
            ->willReturn($repository);
    }

    /**
     * @param MockObject&RefreshToken $refreshToken
     */
    private function setUpRefreshTokenManagerMock(MockObject $refreshToken): void
    {
        $this->refreshTokenManager->expects($this->once())
            ->method('delete')
            ->with($refreshToken);
    }
}
