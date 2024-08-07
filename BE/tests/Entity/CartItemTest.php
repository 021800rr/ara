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
    }

    public function testValidCartItem(): void
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(10.0);

        $cart = new Cart();
        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setCart($cart);
        $cartItem->setQuantity(2);
        $cartItem->setPrice(10.0);

        $errors = $this->validator->validate($cartItem);
        $this->assertCount(0, $errors);
    }

    public function testInvalidCartItem(): void
    {
        $cartItem = new CartItem();
        // Missing product, cart, quantity, and price

        $errors = $this->validator->validate($cartItem);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testInvalidQuantity(): void
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(10.0);

        $cart = new Cart();
        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setCart($cart);
        $cartItem->setQuantity(-1); // Invalid quantity
        $cartItem->setPrice(10.0);

        $errors = $this->validator->validate($cartItem);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testInvalidPrice(): void
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(10.0);

        $cart = new Cart();
        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setCart($cart);
        $cartItem->setQuantity(2);
        $cartItem->setPrice(-5.0); // Invalid price

        $errors = $this->validator->validate($cartItem);
        $this->assertGreaterThan(0, count($errors));
    }
}
