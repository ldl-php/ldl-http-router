<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result;

interface RoutePathParsingResultInterface
{
    public function isDynamic(): bool;

    public function getPath(): string;

    public function getPlaceHolders(): ?array;
}
