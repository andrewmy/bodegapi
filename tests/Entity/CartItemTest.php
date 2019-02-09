<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class CartItemTest extends TestCase
{
    public function testMoney(): void
    {
        $cartItem = (new CartItem())
            ->setProduct(
                (new Product())
                    ->setPrice(new Money(1, 20))
                    ->setVatRate(0.1)
            )
            ->setQuantity(2);

        $this->assertEquals(new Money(2, 40), $cartItem->getSubtotal());
        $this->assertEquals(new Money(0, 24), $cartItem->getVatAmount());
        $this->assertEquals(new Money(2, 64), $cartItem->getTotal());
    }
}
