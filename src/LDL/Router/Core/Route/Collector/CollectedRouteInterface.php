<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collector;

use LDL\Router\Core\Route\RouteInterface;

interface CollectedRouteInterface
{
    public function getPaths(): array;

    public function getRoute(): RouteInterface;
}
