<?php

declare(strict_types=1);

namespace App\Entity\Interfaces;

use App\Entity\ValueObject\Interfaces\MoneyInterface;

interface ProductInterface
{
    public function setName(string $name): self;

    public function getName(): string;

    public function setAvailable(int $available): self;

    public function getAvailable(): int;

    public function setPrice(MoneyInterface $price): self;

    public function getPrice(): MoneyInterface;

    public function setVatRate(float $vat): self;

    public function getVatRate(): float;
}
