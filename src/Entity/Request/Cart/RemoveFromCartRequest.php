<?php

declare(strict_types=1);

namespace App\Entity\Request\Cart;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\CartItem;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "post" = {
 *             "access_control" = "is_granted('ROLE_USER')",
 *             "path" = "/cart/remove",
 *             "swagger_context" = {"tags" = {"Cart"}}
 *         }
 *     },
 *     itemOperations={},
 *     normalizationContext={"groups" = {"output"}},
 *     denormalizationContext={"groups" = {"input"}}
 * )
 */
class RemoveFromCartRequest
{
    /**
     * @var int
     *
     * @Assert\NotBlank
     *
     * @Groups({"input"})
     */
    public $productId;

    /**
     * @var int
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(value="0")
     *
     * @Groups({"input"})
     */
    public $quantity;

    /**
     * @var CartItem
     *
     * @Groups({"output"})
     */
    public $cartItem;
}
