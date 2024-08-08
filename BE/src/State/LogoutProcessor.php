<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\TokenDto;
use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @implements ProcessorInterface<TokenDto, void>
 */
final readonly class LogoutProcessor implements ProcessorInterface
{
    public function __construct(
        private CacheInterface $cache,
        #[Autowire('%jwt_token_ttl%')] private int $jwtTtl,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[\Override] public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if (!$data instanceof TokenDto) {
            throw new \InvalidArgumentException('Expected instance of TokenDto');
        }

        $this->logger->debug('Adding token to blacklist', ['token' => $data->token]);
        $this->cache->get($data->token, function (ItemInterface $item): void {
            $item->expiresAfter($this->jwtTtl);
        });
        $email = $this->decode($data->token);
        $this->remove($email);
    }

    private function decode(string $token): string
    {
        $message = 'Cannot get username from access token';
        $parts = explode(".", $token);
        if ((!isset($parts[1])) || (!$json = base64_decode($parts[1]))) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $message, null, [], 400);
        }
        /** @var object{
         *      username: string
         * } $jwtPayload
         */
        $jwtPayload = json_decode($json);
        if (!(is_object($jwtPayload) && is_string($jwtPayload->username))) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $message, null, [], 400);
        }
        $this->logger->debug('Decoded JWT token', ['username' => $jwtPayload->username]);

        return $jwtPayload->username;
    }

    private function remove(string $email): void
    {
        foreach ($this->entityManager
                     ->getRepository(RefreshToken::class)
                     ->findBy(['username' => $email]) as $refreshToken
        ) {
            $this->logger->debug(
                'Deleting refresh token',
                ['refresh_token' => $refreshToken->getRefreshToken()]
            );
            $this->refreshTokenManager->delete($refreshToken);
        }
    }
}
