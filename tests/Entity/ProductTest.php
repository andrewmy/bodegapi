<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testVatAmount(): void
    {
        /** @var Product $product */
        $product = (new Product())
            ->setPrice(new Money(1, 20))
            ->setVatRate(0.1);

        $this->assertEquals(new Money(0, 12), $product->getVatAmount());
    }
}
