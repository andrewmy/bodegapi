<?php

declare(strict_types=1);

namespace App\Tests\ValueObject;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\ValueObject\Money;
use App\ValueObject\UserCart;
use PHPUnit\Framework\TestCase;

class UserCartTest extends TestCase
{
    /** @var UserCart */
    private $cart;

    public function setUp(): void
    {
        $this->cart = new UserCart(new User());
    }

    public function testAddProduct(): void
    {
        $product1 = new Product();
        $product2 = new Product();

        $this->assertCount(0, $this->cart->getProducts());

        $this->cart->addProduct($product1);
        $this->assertCount(1, $this->cart->getProducts());
        $this->assertSame($product1, $this->cart->getProducts()[0]->getProduct());
        $this->assertSame(1, $this->cart->getProducts()[0]->getQuantity());

        $this->cart->addProduct($product1);
        $this->assertCount(1, $this->cart->getProducts());
        $this->assertSame($product1, $this->cart->getProducts()[0]->getProduct());
        $this->assertSame(2, $this->cart->getProducts()[0]->getQuantity());

        $this->cart->addProduct($product2);
        $this->assertCount(2, $this->cart->getProducts());
        $this->assertSame($product2, $this->cart->getProducts()[1]->getProduct());
        $this->assertSame(1, $this->cart->getProducts()[1]->getQuantity());
        $this->assertSame(2, $this->cart->getProducts()[0]->getQuantity());
    }

    public function testRemoveProduct(): void
    {
        $product1 = new Product();

        $this->cart->addProduct($product1);
        $this->cart->removeProduct($product1);
        $this->assertCount(0, $this->cart->getProducts());

        $this->cart->addProduct($product1);
        $this->cart->addProduct($product1);
        $this->cart->removeProduct($product1);
        $this->assertCount(1, $this->cart->getProducts());
        $this->assertSame(1, $this->cart->getProducts()[0]->getQuantity());
    }

    public function testMoney(): void
    {
        $product1 = (new Product())
            ->setPrice(new Money(1, 20))
            ->setVatRate(0.1);
        $product2 = (new Product())
            ->setPrice(new Money(0, 50))
            ->setVatRate(0.2);
        $this->cart->addProduct($product1);
        $this->cart->addProduct($product2);
        $this->cart->addProduct($product2);

        $this->assertEquals(new Money(2, 20), $this->cart->getSubtotal());
        $this->assertEquals(new Money(0, 32), $this->cart->getVatAmount());
        $this->assertEquals(new Money(2, 52), $this->cart->getTotal());
    }
}
