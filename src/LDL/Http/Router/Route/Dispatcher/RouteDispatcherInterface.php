<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface RouteDispatcherInterface
{
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) :?array;
}
