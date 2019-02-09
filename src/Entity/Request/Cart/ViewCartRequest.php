<?php

declare(strict_types=1);

namespace App\Entity\Request\Cart;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\CartItem;
use App\Entity\ValueObject\Interfaces\MoneyInterface;
use App\Entity\ValueObject\Money;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get" = {
 *             "access_control" = "is_granted('ROLE_USER')",
 *             "path" = "/cart",
 *             "swagger_context" = {"tags" = {"Cart"}}
 *         }
 *     },
 *     itemOperations={},
 * )
 */
class ViewCartRequest
{
    /**
     * @var CartItem[]|array[]
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context" = {
     *             "type" = "array",
     *             "items" = {
     *                 "$ref" = "#/definitions/CartItem-output"
     *             }
     *         }
     *     }
     * )
     */
    public $items = [];

    /**
     * @var MoneyInterface|array
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context" = {
     *             "type" = "object",
     *             "properties" = {
     *                 "euros" = {"type" = "integer"},
     *                 "cents" = {"type" = "integer"}
     *             }
     *         }
     *     }
     * )
     */
    public $subTotal;

    /**
     * @var MoneyInterface|array
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context" = {
     *             "type" = "object",
     *             "properties" = {
     *                 "euros" = {"type" = "integer"},
     *                 "cents" = {"type" = "integer"}
     *             }
     *         }
     *     }
     * )
     */
    public $vatAmount;

    /**
     * @var MoneyInterface|array
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context" = {
     *             "type" = "object",
     *             "properties" = {
     *                 "euros" = {"type" = "integer"},
     *                 "cents" = {"type" = "integer"}
     *             }
     *         }
     *     }
     * )
     */
    public $total;

    public function __construct()
    {
        $this->subTotal = new Money(0, 0);
        $this->vatAmount = new Money(0, 0);
        $this->total = new Money(0, 0);
    }
}
