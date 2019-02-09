<?php

declare(strict_types=1);

namespace App\Filter;

use App\Annotation\UserAware;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * @method mixed getParameter(string $name)
 */
class UserFilter extends SQLFilter
{
    /** @var Reader */
    private $annotationReader;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param ClassMetadata $targetEntity
     * @param string        $targetTableAlias
     *
     * @return string
     */
    public function addFilterConstraint(
        ClassMetadata $targetEntity, $targetTableAlias
    ) {
        if (null === $this->annotationReader) {
            throw new \RuntimeException('Set annotation reader');
        }

        /** @var UserAware|null $aware */
        $aware = $this->annotationReader->getClassAnnotation(
            $targetEntity->getReflectionClass(), UserAware::class
        );
        if (null === $aware) {
            return '';
        }

        try {
            $userIds = (string) $this->getParameter('ids');
        } catch (\InvalidArgumentException $exception) {
            return '';
        }

        $fieldName = $aware->userFieldName;
        if ('' === $fieldName || '' === $userIds) {
            return '';
        }

        if (false !== \mb_strpos($fieldName, '.')) {
            list($relName, $relField) = \explode('.', $fieldName, 2);
            $relation = $targetEntity->getAssociationMapping($relName);

            return \sprintf(
                '(SELECT DISTINCT sub_j.%s FROM %s AS sub_m LEFT JOIN %s AS sub_j '
                    .'ON sub_j.%s = sub_m.%s) IN(%s)',
                $relField,
                $targetEntity->getTableName(),
                $this->entityManager->getClassMetadata($relation['targetEntity'])
                    ->getTableName(),
                $relation['joinColumns'][0]['referencedColumnName'],
                $relation['joinColumns'][0]['name'],
                $userIds
            );
        }

        return \sprintf(
            '%s.%s IN(%s)', $targetTableAlias, $fieldName, $userIds
        );
    }

    public function setAnnotationReader(Reader $reader): self
    {
        $this->annotationReader = $reader;

        return $this;
    }

    public function setEntityManager(
        EntityManagerInterface $entityManager
    ): self {
        $this->entityManager = $entityManager;

        return $this;
    }
}
