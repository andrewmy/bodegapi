<?php

declare(strict_types=1);

require \dirname(__DIR__).'/config/bootstrap.php';

if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    \passthru(\sprintf(
        'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
        __DIR__,
        $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
    ));
}

\App\Tests\DatabaseAwareTestTrait::createDatabase();
