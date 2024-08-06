<?php

namespace App\Tests\Repository;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\SaveRemoveTrait;
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
            use SaveRemoveTrait;

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
            use SaveRemoveTrait;

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

    public function testRemove(): void
    {
        $product = new Product();
        $product->setName('Test Product');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($product));
        $entityManager->expects($this->once())
            ->method('flush');

        $repository = new class($entityManager) {
            use SaveRemoveTrait;

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

        $repository->remove($product, true);
    }

    public function testRemoveWithoutFlush(): void
    {
        $cart = new Cart();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($cart));
        $entityManager->expects($this->never())
            ->method('flush');

        $repository = new class($entityManager) {
            use SaveRemoveTrait;

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

        $repository->remove($cart, false);
    }
}
