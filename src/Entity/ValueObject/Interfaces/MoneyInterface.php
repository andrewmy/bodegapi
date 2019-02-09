<?php

declare(strict_types=1);

namespace App\Entity\ValueObject\Interfaces;

interface MoneyInterface
{
    public function setCents(int $cents): self;

    public function getCents(): int;

    public function setEuros(int $euros): self;

    public function getEuros(): int;
}
