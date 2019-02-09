<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use App\Annotation\UserAware;
use App\Entity\CartItem;
use App\Filter\UserFilter;
use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\FilterCollection;
use PHPUnit\Framework\TestCase;

class UserFilterTest extends TestCase
{
    public function testAddsFilterConstraintWithRelation(): void
    {
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $targetMeta = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $relationMeta = clone $targetMeta;
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aware = new UserAware();
        $aware->userFieldName = 'rel.user_id';

        $reader
            ->method('getClassAnnotation')
            ->willReturn($aware);

        $targetMeta
            ->method('getAssociationMapping')
            ->willReturn([
                'targetEntity' => 'rel',
                'joinColumns' => [[
                    'referencedColumnName' => 'id',
                    'name' => 'rel_id',
                ]],
            ]);
        $targetMeta
            ->method('getTableName')
            ->willReturn('cart_item');
        $targetMeta
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(CartItem::class));

        $relationMeta
            ->method('getTableName')
            ->willReturn('rel');

        $entityManager
            ->method('getClassMetadata')
            ->willReturn($relationMeta);
        $entityManager
            ->method('getFilters')
            ->willReturn(new FilterCollection($entityManager));
        $entityManager
            ->method('getConnection')
            ->willReturn($connection);

        $filter = new UserFilter($entityManager);
        $filter->setAnnotationReader($reader);
        $filter->setEntityManager($entityManager);
        $filter->setParameter('ids', '');

        $this->assertSame(
            '', $filter->addFilterConstraint($targetMeta, 'cart_item')
        );

        $filter->setParameter('ids', 1);

        $connection
            ->method('quote')
            ->with(1, 'integer')
            ->willReturn("'1'");

        $this->assertSame(
            '(SELECT DISTINCT sub_j.user_id FROM cart_item AS sub_m '
            ."LEFT JOIN rel AS sub_j ON sub_j.id = sub_m.rel_id) IN('1')",
            $filter->addFilterConstraint($targetMeta, 'cart_item')
        );

        $aware->userFieldName = 'user_id';

        $this->assertSame(
            "cart_item.user_id IN('1')",
            $filter->addFilterConstraint($targetMeta, 'cart_item')
        );
    }
}
