<?php

namespace App\Tests\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\CurrentCartExtension;
use App\Entity\Cart;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;

class CurrentCartExtensionTest extends TestCase
{
    private CurrentCartExtension $currentCartExtension;
    private QueryBuilder $queryBuilder;
    private User $user;

    protected function setUp(): void
    {
        $security = $this->createMock(Security::class);
        $this->user = $this->createMock(User::class);

        $security->method('getUser')->willReturn($this->user);

        $this->currentCartExtension = new CurrentCartExtension($security);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = new QueryBuilder($entityManager);
    }

    public function testApplyToCollection(): void
    {
        $this->queryBuilder->select('c')
            ->from(Cart::class, 'c');

        $this->currentCartExtension->applyToCollection(
            $this->queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Cart::class
        );

        $this->assertSame(
            'SELECT c FROM ' . Cart::class . ' c WHERE c.user = :user',
            $this->queryBuilder->getDQL()
        );

        $userParam = $this->queryBuilder->getParameter('user');
        $this->assertNotNull($userParam);

        if ($userParam !== null) {
            $this->assertSame($this->user, $userParam->getValue());
        }
    }

    public function testApplyToItem(): void
    {
        $this->queryBuilder->select('c')
            ->from(Cart::class, 'c');

        $this->currentCartExtension->applyToItem(
            $this->queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Cart::class,
            []
        );

        $this->assertSame(
            'SELECT c FROM ' . Cart::class . ' c WHERE c.user = :user',
            $this->queryBuilder->getDQL()
        );

        $userParam = $this->queryBuilder->getParameter('user');
        $this->assertNotNull($userParam);

        if ($userParam !== null) {
            $this->assertSame($this->user, $userParam->getValue());
        }
    }

    public function testApplyToItemWithIdentifiers(): void
    {
        $this->queryBuilder->select('c')
            ->from(Cart::class, 'c');

        $this->currentCartExtension->applyToItem(
            $this->queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Cart::class,
            ['id' => 1]
        );

        $this->assertSame(
            'SELECT c FROM ' . Cart::class . ' c WHERE c.user = :user',
            $this->queryBuilder->getDQL()
        );

        /** @var Parameter|null $userParam */
        $userParam = $this->queryBuilder->getParameter('user');
        $this->assertNotNull($userParam);
        if ($userParam !== null) {
            $this->assertSame($this->user, $userParam->getValue());
        }
    }


    public function testUserIsNull(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);
        $currentCartExtension = new CurrentCartExtension($security);

        $this->queryBuilder->select('c')
            ->from(Cart::class, 'c');

        $currentCartExtension->applyToCollection(
            $this->queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Cart::class
        );

        $this->assertStringNotContainsString('c.user = :user', $this->queryBuilder->getDQL());
    }

    public function testDoesNotApplyToNonCartResource(): void
    {
        $this->queryBuilder->select('u')
            ->from(User::class, 'u');

        $this->currentCartExtension->applyToCollection(
            $this->queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            User::class
        );

        $this->assertSame(
            'SELECT u FROM ' . User::class . ' u',
            $this->queryBuilder->getDQL()
        );
    }

    public function testDoesNotApplyToItemNonCartResource(): void
    {
        $this->queryBuilder->select('u')
            ->from(User::class, 'u');

        $this->currentCartExtension->applyToItem(
            $this->queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            User::class,
            []
        );

        $this->assertSame(
            'SELECT u FROM ' . User::class . ' u',
            $this->queryBuilder->getDQL()
        );
    }
}
