<?php

declare(strict_types=1);

namespace App\Tests;

trait DatabaseAwareTestTrait
{
    /** @var array */
    protected static $loadedFixtures = [];

    public static function dropDatabase(): void
    {
        \passthru(\sprintf(
            'php "%s/../bin/console" doctrine:schema:drop --force --env=test',
            __DIR__
        ));
    }

    public static function createDatabase(): void
    {
        self::dropDatabase();

        if (0 === \mb_strpos($_ENV['DATABASE_URL'], 'sqlite:')) {
            \passthru(\sprintf(
                'php "%s/../bin/console" doctrine:schema:create --env=test',
                __DIR__
            ));
        } else {
            \passthru(\sprintf(
                'php "%s/../bin/console" doctrine:query:sql "DROP TABLE IF EXISTS migration_versions" -n --env=test',
                __DIR__
            ));
            \passthru(\sprintf(
                'php "%s/../bin/console" doctrine:migrations:migrate -n --env=test',
                __DIR__
            ));
        }

        \passthru(\sprintf(
            'php "%s/../bin/console" doctrine:fixtures:load -n --env=test',
            __DIR__
        ));
    }
}
