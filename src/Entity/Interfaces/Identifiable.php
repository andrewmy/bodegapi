<?php

declare(strict_types=1);

namespace App\Entity\Interfaces;

interface Identifiable
{
    public function getId(): ?int;
}
