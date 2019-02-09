<?php

declare(strict_types=1);

namespace App\Tests\Entity\ValueObject;

use App\Entity\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testValidInit(): void
    {
        $money = new Money(1, 20);
        $this->assertSame(1, $money->getEuros());
        $this->assertSame(20, $money->getCents());
    }

    public function testInvalidEuros(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $money = new Money(-1, 0);
    }

    public function testInvalidCents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $money = new Money(1, 100);
    }

    public function testAdd(): void
    {
        $this->assertEquals(
            new Money(3, 20),
            (new Money(1, 50))->add(new Money(1, 70))
        );
    }

    public function testMultiply(): void
    {
        $this->assertEquals(
            new Money(4, 00),
            (new Money(1, 60))->multiply(2.5)
        );
    }
}
