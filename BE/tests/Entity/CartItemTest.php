<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Tests\SetUpTrait;

class CartItemTest extends ApiTestCase
{
    use SetUpTrait;

    protected function setUp(): void
    {
        $this->setUpValidator();
        $this->setUpRepositories();

        /** @var Product $product */
        $product = $this->productRepository->find(self::PRODUCT_ID_1);
        $this->product = $product;

        $this->cart = new Cart();
        $this->cartItem = new CartItem();
    }

    public function testValidCartItem(): void
    {
        $this->cartItem->setProduct($this->product);
        $this->cartItem->setCart($this->cart);
        $this->cartItem->setQuantity(2);
        $this->cartItem->setPrice($this->product->getPrice());

        $errors = $this->validator->validate($this->cartItem);
        $this->assertCount(0, $errors);
    }

    public function testInvalidCartItem(): void
    {
        // Missing product, cart, quantity, and price

        $errors = $this->validator->validate($this->cartItem);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testInvalidQuantity(): void
    {
        $this->cartItem->setProduct($this->product);
        $this->cartItem->setCart($this->cart);
        $this->cartItem->setQuantity(-1); // Invalid quantity
        $this->cartItem->setPrice($this->product->getPrice());

        $errors = $this->validator->validate($this->cartItem);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testInvalidPrice(): void
    {
        $this->cartItem->setProduct($this->product);
        $this->cartItem->setCart($this->cart);
        $this->cartItem->setQuantity(2);
        $this->cartItem->setPrice(-5.0); // Invalid price

        $errors = $this->validator->validate($this->cartItem);
        $this->assertGreaterThan(0, count($errors));
    }
}
