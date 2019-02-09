<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("CLASS")
 *
 * @codeCoverageIgnore
 */
final class UserAware
{
    /** @var string */
    public $userFieldName;
}
