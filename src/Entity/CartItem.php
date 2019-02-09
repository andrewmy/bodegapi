<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Annotation\UserAware;
use App\Entity\Interfaces\Identifiable;
use App\Entity\Interfaces\ProductInterface;
use App\Entity\Interfaces\Timestampable;
use App\Entity\Traits\HasId;
use App\Entity\Traits\HasTimestamps;
use App\Entity\ValueObject\Interfaces\MoneyInterface;
use App\Entity\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CartItemRepository")
 *
 * @UserAware(userFieldName="user_id")
 *
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get" = {"access_control" = "is_granted('ROLE_USER')"},
 *     },
 *     normalizationContext={"groups" = {"output"}}
 * )
 */
class CartItem implements Identifiable, Timestampable
{
    use HasId, HasTimestamps;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="cartItems"
     * )
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull
     */
    private $user;

    /**
     * @var ProductInterface
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Product",
     *     inversedBy="cartItems"
     * )
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull
     *
     * @Groups({"output"})
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"output"})
     */
    private $quantity = 0;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function setProduct(ProductInterface $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSubtotal(): MoneyInterface
    {
        /** @var Money $price */
        $price = $this->getProduct()->getPrice();

        return $price->multiply($this->getQuantity());
    }

    public function getVatAmount(): MoneyInterface
    {
        $product = $this->getProduct();
        \assert($product instanceof Product);
        /** @var Money $vat */
        $vat = $product->getVatAmount();

        return $vat->multiply($this->getQuantity());
    }

    public function getTotal(): MoneyInterface
    {
        /** @var Money $sub */
        $sub = $this->getSubtotal();

        return $sub->add($this->getVatAmount());
    }
}
