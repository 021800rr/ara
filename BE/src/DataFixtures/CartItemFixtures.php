<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CartItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Cart $cart */
        $cart = $this->getReference('cart');
        /** @var Product $product1*/
        $product1 = $this->getReference('product1');
        /** @var Product $product2*/
        $product2 = $this->getReference('product2');

        $cartItem1 = new CartItem();
        $cartItem1->setProduct($product1);
        $cartItem1->setCart($cart);
        $cartItem1->setQuantity(1);
        $cartItem1->setPrice($product1->getPrice());

        $cartItem2 = new CartItem();
        $cartItem2->setProduct($product2);
        $cartItem2->setCart($cart);
        $cartItem2->setQuantity(2);
        $cartItem2->setPrice($product2->getPrice());

        $manager->persist($cartItem1);
        $manager->persist($cartItem2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CartFixtures::class,
            ProductFixtures::class,
        ];
    }
}
