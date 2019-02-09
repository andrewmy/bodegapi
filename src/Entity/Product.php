<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Interfaces\Identifiable;
use App\Entity\Interfaces\ProductInterface;
use App\Entity\Interfaces\Timestampable;
use App\Entity\Traits\HasId;
use App\Entity\Traits\HasTimestamps;
use App\Entity\ValueObject\Interfaces\MoneyInterface;
use App\Entity\ValueObject\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"name"})
 * })
 *
 * @UniqueEntity(fields={"name"})
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get" = {},
 *         "post" = {"access_control" = "is_granted('ROLE_ADMIN')"}
 *     },
 *     itemOperations={
 *         "get" = {},
 *         "put" = {"access_control" = "is_granted('ROLE_ADMIN')"},
 *         "delete" = {"access_control" = "is_granted('ROLE_ADMIN')"}
 *     },
 *     normalizationContext={"groups" = {"output"}},
 *     denormalizationContext={"groups" = {"input"}}
 * )
 */
class Product implements Identifiable, Timestampable, ProductInterface
{
    use HasId, HasTimestamps;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     *
     * @Groups({"output", "input"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     *
     * @Groups({"output", "input"})
     */
    private $available = 0;

    /**
     * @var MoneyInterface
     *
     * @ORM\Embedded(class="App\Entity\ValueObject\Money")
     *
     * @Assert\NotBlank
     *
     * @Groups({"output", "input"})
     * @ApiProperty(
     *     attributes={
     *         "swagger_context" = {
     *             "type" = "object",
     *             "properties" = {
     *                 "euros" = {"type" = "integer", "example" = 123},
     *                 "cents" = {"type" = "integer", "example" = 45}
     *             }
     *         }
     *     }
     * )
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank
     *
     * @Groups({"output", "input"})
     * @ApiProperty(
     *     attributes={
     *         "swagger_context" = {
     *             "type" = "float",
     *             "example" = 0.14
     *         }
     *     }
     * )
     */
    private $vatRate = 0.0;

    /**
     * @var Collection|CartItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\CartItem",
     *     mappedBy="product",
     *     orphanRemoval=true
     * )
     */
    private $cartItems;

    public function __construct()
    {
        $this->price = new Money(0, 0);
        $this->cartItems = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ProductInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function setAvailable(int $available): ProductInterface
    {
        $this->available = $available;

        return $this;
    }

    public function getPrice(): MoneyInterface
    {
        return $this->price;
    }

    public function setPrice(MoneyInterface $price): ProductInterface
    {
        $this->price = $price;

        return $this;
    }

    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    public function setVatRate(float $vatRate): ProductInterface
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    public function getVatAmount(): MoneyInterface
    {
        \assert($this->price instanceof Money);

        return $this->price->multiply($this->vatRate);
    }
}
