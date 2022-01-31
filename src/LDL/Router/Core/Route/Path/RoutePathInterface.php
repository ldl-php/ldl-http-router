<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path;

interface RoutePathInterface
{
    public function getPath(): string;
}
