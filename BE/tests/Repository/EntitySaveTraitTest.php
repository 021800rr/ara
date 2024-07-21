<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\EntitySaveTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EntitySaveTraitTest extends TestCase
{
    public function testSave(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($user));
        $entityManager->expects($this->once())
            ->method('flush');

        $repository = new class($entityManager) {
            use EntitySaveTrait;

            private EntityManagerInterface $entityManager;

            public function __construct(EntityManagerInterface $entityManager)
            {
                $this->entityManager = $entityManager;
            }

            protected function getEntityManager(): EntityManagerInterface
            {
                return $this->entityManager;
            }
        };

        $repository->save($user, true);
    }

    public function testSaveWithoutFlush(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($user));
        $entityManager->expects($this->never())
            ->method('flush');

        $repository = new class($entityManager) {
            use EntitySaveTrait;

            private EntityManagerInterface $entityManager;

            public function __construct(EntityManagerInterface $entityManager)
            {
                $this->entityManager = $entityManager;
            }

            protected function getEntityManager(): EntityManagerInterface
            {
                return $this->entityManager;
            }
        };

        $repository->save($user, false);
    }
}
