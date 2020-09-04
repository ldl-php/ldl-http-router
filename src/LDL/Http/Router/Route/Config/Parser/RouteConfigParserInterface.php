<?php

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\Route;
use Psr\Container\ContainerInterface;

interface RouteConfigParserInterface
{
    /**
     * @param array $data
     * @param Route $route
     * @param ContainerInterface|null $container
     * @param string|null $file
     */
    public function parse(
        array $data,
        Route $route,
        ContainerInterface $container = null,
        string $file = null
    ): void;
}
