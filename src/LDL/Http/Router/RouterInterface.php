<?php

declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Router\Route\Collection\RouteCollectionInterface;

interface RouterInterface
{
    public function getRoutes(): RouteCollectionInterface;

    public function dispatch(string $path);
}
