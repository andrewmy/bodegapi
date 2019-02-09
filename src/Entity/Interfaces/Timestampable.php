<?php

declare(strict_types=1);

namespace App\Entity\Interfaces;

interface Timestampable
{
    public function getCreatedAt(): \DateTime;

    public function setCreatedAt(\DateTime $createdAt): self;

    public function getUpdatedAt(): \DateTime;

    public function setUpdatedAt(\DateTime $updatedAt): self;
}
