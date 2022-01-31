<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Collection;

use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Type\Collection\TypedCollectionInterface;

interface RouteDispatcherCollectionInterface extends TypedCollectionInterface
{
    public function dispatch(CollectedRouteInterface $cr, string ...$params): RouteDispatcherResultCollectionInterface;
}
