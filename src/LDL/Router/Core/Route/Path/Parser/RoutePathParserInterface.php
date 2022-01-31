<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Parser;

use LDL\Router\Core\Route\Collection\RouteCollectionInterface;
use LDL\Router\Core\Route\Path\Result\Collection\RoutePathMatchingCollectionInterface;
use LDL\Router\Core\Route\Path\Result\RoutePathParsingResultInterface;

interface RoutePathParserInterface
{
    public function getPathSeparator(): string;

    /**
     * Returns a parsed regex from a route path, for example:.
     *
     * /frontend/:name would be translated as /frontend/^([\w|\d])+$
     * /frontend/[A-Z]+:name would be translated as /frontend/([A-Z])+
     *
     * @param string ...$paths
     */
    public function parse(string ...$paths): RoutePathParsingResultInterface;

    /**
     * @param RouteCollectionInterface $routes
     */
    public function match(
        string $requestedPath,
        iterable $routes
    ): RoutePathMatchingCollectionInterface;
}
