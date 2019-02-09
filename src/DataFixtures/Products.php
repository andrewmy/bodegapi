<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ValueObject\Money;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class Products extends Fixture
{
    /** @var float */
    private $defaultVat;

    public function __construct(float $defaultVat)
    {
        $this->defaultVat = $defaultVat;
    }

    public function load(ObjectManager $manager): void
    {
        $list = [
            [
                'name' => 'Box of apples',
                'available' => 14,
                'price' => new Money(15, 99),
                'vat' => $this->defaultVat,
            ],
            [
                'name' => 'Malduguns 500 ml',
                'available' => 564,
                'price' => new Money(2, 54),
                'vat' => $this->defaultVat,
            ],
            [
                'name' => 'Ibumetin 400',
                'available' => 2,
                'price' => new Money(2, 14),
                'vat' => 0.05,
            ],
        ];

        foreach ($list as $item) {
            $product = (new Product())
                ->setName($item['name'])
                ->setAvailable($item['available'])
                ->setPrice($item['price'])
                ->setVatRate($item['vat']);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
