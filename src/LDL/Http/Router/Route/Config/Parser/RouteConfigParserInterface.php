<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;

interface RouteConfigParserInterface
{
    /**
     * @param RouteInterface $route
     * @throws \Exception
     */
    public function parse(RouteInterface $route) : void;

}
