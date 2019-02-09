<?php

declare(strict_types=1);

namespace App\Entity\ValueObject;

use App\Entity\ValueObject\Interfaces\MoneyInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Embeddable
 */
class Money implements MoneyInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"output", "input"})
     */
    private $euros;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"output", "input"})
     */
    private $cents;

    public function __construct(int $euros, int $cents)
    {
        $this->setEuros($euros);
        $this->setCents($cents);
    }

    public function setCents(int $cents): MoneyInterface
    {
        if ($cents < 0 || $cents > 99) {
            throw new \InvalidArgumentException(
                'Cents can be between 0 and 99'
            );
        }

        $this->cents = $cents;

        return $this;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function setEuros(int $euros): MoneyInterface
    {
        if ($euros < 0) {
            throw new \InvalidArgumentException('Euros must a be positive number');
        }

        $this->euros = $euros;

        return $this;
    }

    public function getEuros(): int
    {
        return $this->euros;
    }

    public function add(MoneyInterface $second): self
    {
        $first = clone $this;
        $total = ($first->getEuros() + $second->getEuros()) * 100
            + $first->getCents() + $second->getCents();
        $first->setEuros((int) ($total / 100))
            ->setCents($total % 100);

        return $first;
    }

    /**
     * @param int|float $multiplier
     *
     * @return Money
     */
    public function multiply($multiplier): self
    {
        $first = clone $this;
        $total = ($first->getEuros() * $multiplier) * 100
            + $first->getCents() * $multiplier;
        $first->setEuros((int) ($total / 100))
            ->setCents($total % 100);

        return $first;
    }
}
