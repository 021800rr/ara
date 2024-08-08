<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CartFixtures extends Fixture implements DependentFixtureInterface
{
    public const string CART = 'cart';

    public function load(ObjectManager $manager): void
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::USER);

        $cart1 = new Cart();
        $cart1->setUser($user);
        $manager->persist($cart1);

        $cart2 = new Cart();
        $cart2->setUser($user);
        $manager->persist($cart2);

        $manager->flush();

        $this->addReference(self::CART, $cart1);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
