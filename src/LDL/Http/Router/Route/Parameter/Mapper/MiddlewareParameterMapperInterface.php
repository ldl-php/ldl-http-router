<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Mapper;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Config\MiddlewareConfigInterface;
use LDL\Http\Router\Route\Parameter\RouteParametersInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface MiddlewareParameterMapperInterface
{
    /**
     * Returns an array with each element being in the correct order needed by $method
     *
     * These parameters are fetched from the router or from the RequestParametersInterface
     *
     * The method to be analyzed needs to be passed
     *
     * $object is provided in case your custom parameter mapper needs knowledge about other properties or methods
     * inside $object.
     *
     * CAUTION: Beware that the operations must be kept simple for maximum performance to maintain
     * the impact of using reflection in performance to a negligible amount.
     *
     * After returning, you can call whatever method in the following way:
     *
     * $this->method(...$arguments); //Being $arguments the value returned by your MiddlewareParameterMapperInterface
     *
     * @param MiddlewareConfigInterface $config
     *
     * @return array|null
     */
    public function map(MiddlewareConfigInterface $config) : ?array;
}