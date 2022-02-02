<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Path;

use LDL\Http\Router\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Http\Router\Route\Path\Result\RoutePathParsingResultInterface;
use LDL\Http\Router\Route\RouteInterface;

interface RoutePathParserInterface
{
    public function getPathSeparator(): string;

    /**
     * Returns a parsed regex from a route path, for example:.
     *
     * /frontend/:name would be translated as ^([\w|\d])+$
     * /frontend/[A-Z]+:name would be translated as ([A-Z])+
     *
     * @param string ...$paths
     */
    public function parse(RouteInterface $route, string ...$paths): RoutePathParsingResultInterface;

    public function match(
        string $requestedPath,
        RoutePathParsingResultInterface $result
    ): ?RoutePathMatchingResultInterface;
}
