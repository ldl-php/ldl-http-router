<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Config\Factory;

use LDL\Http\Router\Middleware\Config\MiddlewareConfigInterface;

interface MiddlewareConfigFactoryInterface{

    /**
     * Creates a MiddlewareConfig object from an array
     *
     * @param array $config
     * @return MiddlewareConfigInterface
     */
    public static function fromArray(array $config) : MiddlewareConfigInterface;

}
