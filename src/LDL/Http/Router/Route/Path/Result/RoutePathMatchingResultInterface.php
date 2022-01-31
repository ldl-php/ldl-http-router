<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Path\Result;

interface RoutePathMatchingResultInterface
{
    public function getResult(): RoutePathParsingResultInterface;

    public function getParameters(): ?array;
}
