<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Factory;

use LDL\Http\Router\Route\Parameter\RouteParameterInterface;

interface RouteParameterFactoryInterface
{

    /**
     * @param array $parameterConfig
     * @param string|null $defaultSource
     * @return RouteParameterInterface
     */
    public static function fromArray(
        array $parameterConfig,
        string $defaultSource = null
    ) : RouteParameterInterface;

}
