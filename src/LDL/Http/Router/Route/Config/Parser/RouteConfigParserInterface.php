<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;

interface RouteConfigParserInterface
{
    /**
     * @param array $config
     * @param RouteInterface $route
     * @param string|null $file
     * @throws \Exception
     */
    public function parse(
        array $config,
        RouteInterface $route,
        string $file = null
    ) : void;
}
