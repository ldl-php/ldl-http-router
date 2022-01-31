<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Result\Collection;

use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Dispatcher\Result\RouteDispatcherResultInterface;
use LDL\Type\Collection\TypedCollectionInterface;

interface RouteDispatcherResultCollectionInterface extends TypedCollectionInterface
{
    public function getCollectedRoute(): CollectedRouteInterface;

    public function findByDispatcherName(string $name): ?RouteDispatcherResultInterface;

    public function getArray(): array;
}
