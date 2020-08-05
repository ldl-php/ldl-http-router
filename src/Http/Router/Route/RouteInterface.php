<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\ParameterCollection;

interface RouteInterface
{
    /**
     * Brief name of the route
     * @return string
     */
    public function getName() : string;

    /**
     * Return a brief description about what does this route do
     * @return string
     */
    public function getDescription() : string;

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards() : ?RouterGuardCollection;

    /**
     * @return array
     */
    public function getMethods() : array;

    /**
     * Route prefix, example: /user/create, this is the prefix that a request must match
     * @return string
     */
    public function getPrefix() : string;

    /**
     * @return ParameterCollection|null
     */
    public function getParameters() : ?ParameterCollection;

    /**
     * Returns the route dispatcher which is in charge of adding logic
     * to the request.
     *
     * You could think of a dispatcher like a controller, although it only contains one single method.
     *
     * @return RouteDispatcherInterface
     */
    public function getDispatcher() : RouteDispatcherInterface;

    public function dispatch(RequestInterface $request, ResponseInterface $response) : void;
}