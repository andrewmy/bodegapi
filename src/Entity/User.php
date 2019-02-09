<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interfaces\Identifiable;
use App\Entity\Interfaces\ProductInterface;
use App\Entity\Interfaces\Timestampable;
use App\Entity\Traits\HasId;
use App\Entity\Traits\HasTimestamps;
use App\ValueObject\UserCart;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, Identifiable, Timestampable
{
    use HasId, HasTimestamps;

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string|null The hashed password
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * A non-persisted field that's used to create the encoded password.
     *
     * @var string|null
     */
    private $plainPassword;

    /**
     * @var Collection|CartItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\CartItem",
     *     mappedBy="user",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    private $cartItems;

    /**
     * @var UserCart
     */
    private $cart;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return \array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->getRoles(), true);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        $this->password = null;

        return $this;
    }

    /**
     * @see UserInterface
     *
     * Not needed
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection|CartItem[]
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function getCartItemByProduct(ProductInterface $product): ?CartItem
    {
        return $this->getCartItems()->filter(
            function (CartItem $item) use ($product) {
                return $item->getProduct() === $product;
            })->first() ?: null;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setUser($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->contains($cartItem)) {
            $this->cartItems->removeElement($cartItem);
            // set the owning side to null (unless already changed)
            if ($cartItem->getUser() === $this) {
                $cartItem->setUser(null);
            }
        }

        return $this;
    }
}
