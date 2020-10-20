<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;
use Psr\Container\ContainerInterface;

interface RouteConfigParserInterface
{
    /**
     * @param array $config
     * @param RouteInterface $route
     * @param ContainerInterface|null $container
     * @param string|null $file
     * @throws \Exception
     */
    public function parse(
        array $config,
        RouteInterface $route,
        ContainerInterface $container=null,
        string $file = null
    ) : void;
}
