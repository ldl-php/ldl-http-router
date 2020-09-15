<?php declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use LDL\Http\Core\Request\Request;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;

class Dispatch implements RouteDispatcherInterface
{
    public function __construct()
    {
    }

    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response
    )
    {
        return [
            'name' => $request->get('name'),
            'age' => $request->get('age')
        ];
    }
}

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response
);

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router
);

$group = new RouteGroup('student', 'student', $routes);

$router->addGroup($group);

$router->dispatch()->send();
