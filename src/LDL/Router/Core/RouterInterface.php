<?php

declare(strict_types=1);

namespace LDL\Router\Core;

use LDL\Router\Core\Exception\RouteNotFoundException;
use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Validators\Chain\ValidatorChainInterface;

interface RouterInterface
{
    public function getRoutes(): RouteCollectionInterface;

    public function getValidatorChain(): ValidatorChainInterface;

    public function find(string $requestedPath): RoutePathMatchingCollectionInterface;

    /**
     * @throws RouteNotFoundException
     */
    public function match(string $path): RouteDispatcherResultCollectionInterface;

    public function getRouteList(): array;
}
