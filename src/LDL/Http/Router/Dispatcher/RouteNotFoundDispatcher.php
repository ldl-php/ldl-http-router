<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Middleware\AbstractMiddleware;
use LDL\Http\Router\Router;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Symfony\Component\HttpFoundation\ParameterBag;

class RouteNotFoundDispatcher extends AbstractMiddleware
{
    private const NAME = 'ldl.http.router.route.not_found';

    public function __construct(string $name=null)
    {
        parent::__construct($name ?? self::NAME);
    }

    public function getContent(): array
    {
        return [];
    }

    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        Router $router,
        ParameterBag $urlParameters = null
    )
    {
        throw new HttpRouteNotFoundException('Route not found');
    }
}
