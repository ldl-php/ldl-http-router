<?php

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\Route;
use Psr\Container\ContainerInterface;

interface RouteConfigParserInterface
{
    /**
     * @throws Exception\RouteConfigParserException
     */
    public function parse(array $data, Route $route, ContainerInterface $container = null): void;
}
