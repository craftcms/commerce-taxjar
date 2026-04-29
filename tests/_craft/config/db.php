<?php

use craft\helpers\App;

return [
    'dsn' => App::env('DB_DSN') ?: null,
    'driver' => App::env('DB_DRIVER') ?? App::env('CRAFT_DB_DRIVER'),
    'server' => App::env('DB_SERVER') ?? App::env('CRAFT_DB_SERVER'),
    'port' => App::env('DB_PORT') ?? App::env('CRAFT_DB_PORT'),
    'database' => App::env('DB_DATABASE') ?? App::env('CRAFT_DB_DATABASE'),
    'user' => App::env('DB_USER') ?? App::env('CRAFT_DB_USER'),
    'password' => App::env('DB_PASSWORD') ?? App::env('CRAFT_DB_PASSWORD'),
    'schema' => App::env('DB_SCHEMA') ?? App::env('CRAFT_DB_SCHEMA'),
    'tablePrefix' => App::env('DB_TABLE_PREFIX') ?? App::env('CRAFT_DB_TABLE_PREFIX'),
];
