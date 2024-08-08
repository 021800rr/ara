<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public const string PRODUCT_1 = 'product1';
    public const string PRODUCT_2 = 'product2';

    public function load(ObjectManager $manager): void
    {
        $product1 = new Product();
        $product1->setName('Product 1');
        $product1->setDescription('Description for product 1');
        $product1->setPrice(10);

        $product2 = new Product();
        $product2->setName('Product 2');
        $product2->setDescription('Description for product 2');
        $product2->setPrice(22.2);

        $product3 = new Product();
        $product3->setName('Product 3');
        $product3->setDescription('Description for product 3');
        $product3->setPrice(30);

        $product4 = new Product();
        $product4->setName('Product 4');
        $product4->setDescription('Description for product 4');
        $product4->setPrice(44.44);

        $manager->persist($product1);
        $manager->persist($product2);
        $manager->persist($product3);
        $manager->persist($product4);

        $manager->flush();

        $this->addReference(self::PRODUCT_1, $product1);
        $this->addReference(self::PRODUCT_2, $product2);
    }
}
