<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result;

use LDL\Router\Core\Route\RouteInterface;

interface RoutePathMatchingResultInterface
{
    public function getRoute(): RouteInterface;

    public function getPath(): RoutePathParsingResultInterface;

    public function getParameters(): ?array;
}
