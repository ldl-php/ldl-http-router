<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result;

use LDL\Router\Core\Route\Collector\CollectedRouteInterface;

interface RoutePathMatchingResultInterface
{
    public function getCollectedRoute(): CollectedRouteInterface;

    public function getPath(): RoutePathParsingResultInterface;

    public function getParameters(): ?array;
}
