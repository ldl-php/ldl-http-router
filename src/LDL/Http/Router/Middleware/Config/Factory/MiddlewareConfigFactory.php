<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Config\Factory;

use LDL\Http\Router\Middleware\Config\MiddlewareConfig;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigInterface;

class MiddlewareConfigFactory implements MiddlewareConfigFactoryInterface
{

    public static function fromArray(array $config) : MiddlewareConfigInterface
    {
        $storeInParameters = array_key_exists('parameter.add', $config) ? (bool) $config['parameter.add'] : MiddlewareConfigInterface::PARAMETER_VALUE_STORE;
        $isPartOfResponse =  array_key_exists('http.response.add', $config) ? (bool) $config['http.response.add'] : MiddlewareConfigInterface::RESPONSE_VALUE_STORE;

        return new MiddlewareConfig(
            [],
            $storeInParameters,
            $isPartOfResponse
        );
    }

}