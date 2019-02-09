<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testRoles(): void
    {
        $user = new User();
        $this->assertEquals([User::ROLE_USER], $user->getRoles());
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertFalse($user->hasRole(User::ROLE_ADMIN));

        $user->setRoles([User::ROLE_ADMIN]);
        $this->assertEquals([User::ROLE_ADMIN, User::ROLE_USER], $user->getRoles());
        $this->assertTrue($user->hasRole(User::ROLE_ADMIN));
    }

    public function testCredentials(): void
    {
        $user = new User();
        $this->assertSame('', $user->getPassword());
        $this->assertNull($user->getPlainPassword());

        $user->setPassword('pass');
        $this->assertSame('pass', $user->getPassword());

        $user->setPlainPassword('pass');
        $this->assertSame('', $user->getPassword());
        $this->assertSame('pass', $user->getPlainPassword());

        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
    }

    public function testGetCartItemByProduct(): void
    {
        $user = new User();
        $product1 = new Product();
        $product2 = new Product();
        $cartItem1 = (new CartItem())->setProduct($product1);
        $cartItem2 = (new CartItem())->setProduct($product2);
        $user
            ->addCartItem($cartItem1)
            ->addCartItem($cartItem2);

        $this->assertSame($cartItem1, $user->getCartItemByProduct($product1));
        $this->assertSame($cartItem2, $user->getCartItemByProduct($product2));
    }
}
