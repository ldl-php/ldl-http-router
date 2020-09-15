<?php declare(strict_types=1);

require __DIR__.'/../../../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;

use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Handler\Exception\Handler\HttpMethodNotAllowedExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\HttpRouteNotFoundExceptionHandler;
use LDL\Http\Router\Handler\Exception\Handler\InvalidContentTypeExceptionHandler;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;

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

/**
 * Class TestingExceptionHandler
 *
 * This exception handler only runs on route testLocalExceptionHandlerRoute
 */
class TestingExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct()
    {
        die("se construye");
    }

    public function getNamespace(): string
    {
        return 'CustomHandler';
    }

    public function getName(): string
    {
        return 'TestingExceptionHandler';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function handle(Router $router, \Exception $e): ?int
    {
        return $e instanceof \Exception ? $e->getCode() : null;
    }
}

/**
 * Add GLOBAL ExceptionHandlers
 */
$exceptionHandlerCollection = new ExceptionHandlerCollection();
$exceptionHandlerCollection->append(new HttpMethodNotAllowedExceptionHandler());
$exceptionHandlerCollection->append(new HttpRouteNotFoundExceptionHandler());
$exceptionHandlerCollection->append(new InvalidContentTypeExceptionHandler());

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response,
    $exceptionHandlerCollection
);

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router
);

$group = new RouteGroup('example', 'example', $routes);

$router->addGroup($group);

$router->dispatch()->send();
