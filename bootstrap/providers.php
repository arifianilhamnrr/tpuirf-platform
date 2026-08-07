<?php

use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Infrastructure\Providers\ServiceServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    ServiceServiceProvider::class,
];
