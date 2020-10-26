<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Contracts\IsActiveInterface;
use LDL\Framework\Base\Contracts\PriorityInterface;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\RouteInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface MiddlewareInterface extends  IsActiveInterface, PriorityInterface
{
    /**
     * @param RouteInterface $route
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag $urlParameters
     * @return array|null
     */
    public function dispatch(
        RouteInterface $route,
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : ?array;

    /**
     * @return string
     */
    public function getName() : string;

}