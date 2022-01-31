<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Collector;

use LDL\Router\Core\Route\Collection\RouteCollectionInterface;

interface RouteCollectorInterface
{
    public function collect(RouteCollectionInterface $collection): iterable;
}
