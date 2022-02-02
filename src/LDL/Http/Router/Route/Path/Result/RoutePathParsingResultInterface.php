<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Path\Result;

use LDL\Http\Router\Route\RouteInterface;

interface RoutePathParsingResultInterface
{
    public function getRoute(): RouteInterface;

    public function isDynamic(): bool;

    public function getPath(): string;

    public function getPlaceHolders(): ?array;
}
