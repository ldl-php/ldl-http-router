<?php

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\Route;

interface RouteConfigParserInterface
{
    /**
     * @param array $data
     * @param Route $route
     *
     * @throws Exception\RouteConfigParserException
     */
    public function parse(array $data, Route $route) : void;
}