<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Entity\CartItem;
use App\Entity\Interfaces\ProductInterface;
use App\Entity\User;
use App\Entity\ValueObject\Interfaces\MoneyInterface;
use App\Entity\ValueObject\Money;
use App\ValueObject\Interfaces\CartInterface;

class UserCart implements CartInterface
{
    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function addProduct(ProductInterface $product): CartInterface
    {
        $cartItem = $this->user->getCartItemByProduct($product);
        if (null === $cartItem) {
            $cartItem = (new CartItem())->setProduct($product);
            $this->user->addCartItem($cartItem);
        }

        $cartItem->setQuantity($cartItem->getQuantity() + 1);

        return $this;
    }

    public function removeProduct(ProductInterface $product): CartInterface
    {
        $cartItem = $this->user->getCartItemByProduct($product);
        if (null === $cartItem) {
            return $this;
        }

        $cartItem->setQuantity($cartItem->getQuantity() - 1);
        if (0 === $cartItem->getQuantity()) {
            $this->user->removeCartItem($cartItem);
        }

        return $this;
    }

    /**
     * @return CartItem[]
     */
    public function getProducts(): array
    {
        return \array_values($this->user->getCartItems()->toArray());
    }

    public function getSubtotal(): MoneyInterface
    {
        return \array_reduce(
            $this->getProducts(),
            function (Money $carry, CartItem $cartItem) {
                return $carry->add($cartItem->getSubtotal());
            }, new Money(0, 0)
        );
    }

    public function getVatAmount(): MoneyInterface
    {
        return \array_reduce(
            $this->getProducts(),
            function (Money $carry, CartItem $cartItem) {
                return $carry->add($cartItem->getVatAmount());
            }, new Money(0, 0)
        );
    }

    public function getTotal(): MoneyInterface
    {
        return \array_reduce(
            $this->getProducts(),
            function (Money $carry, CartItem $cartItem) {
                return $carry->add($cartItem->getTotal());
            }, new Money(0, 0)
        );
    }
}
