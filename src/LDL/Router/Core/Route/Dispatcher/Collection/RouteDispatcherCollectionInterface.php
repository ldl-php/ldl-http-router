<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Collection;

use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Type\Collection\TypedCollectionInterface;

interface RouteDispatcherCollectionInterface extends TypedCollectionInterface
{
    public function getByName(string $name): ?RouteDispatcherInterface;

    public function filterByNames(iterable $names): RouteDispatcherCollectionInterface;

    public function dispatch(CollectedRouteInterface $cr, string ...$params): RouteDispatcherResultCollectionInterface;
}
