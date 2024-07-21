<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product1 = new Product();
        $product1->setName('Product 1');
        $product1->setDescription('Description for product 1');
        $product1->setPrice('USD', 10.0);
        $product1->setPrice('EUR', 8.5);

        $product2 = new Product();
        $product2->setName('Product 2');
        $product2->setDescription('Description for product 2');
        $product2->setPrice('USD', 20.0);
        $product2->setPrice('EUR', 17.0);

        $manager->persist($product1);
        $manager->persist($product2);

        $manager->flush();
    }
}
