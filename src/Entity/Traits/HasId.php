<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Symfony\Component\Serializer\Annotation\Groups;

trait HasId
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"output"})
     */
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
