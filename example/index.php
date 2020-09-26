<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

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
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Middleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;

class Dispatcher implements RouteDispatcherInterface
{
    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response
    ) : ?array
    {
        return [
            'name' => $request->get('name')
        ];
    }
}

class PreDispatch implements MiddlewareInterface
{
    public function getNamespace(): string
    {
        return 'PreDispatchNamespace';
    }

    public function getName(): string
    {
        return 'PreDispatchName';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    )
    {
        return 'pre dispatch result!';
    }
}

class PostDispatch implements MiddlewareInterface
{
    public function getNamespace(): string
    {
        return 'PostDispatchNamespace';
    }

    public function getName(): string
    {
        return 'PostDispatchName';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArguments = []
    )
    {
        return 'post dispatch result!';
    }
}

/**
 * Class ConfigParser
 *
 * Useful for plugin developers to implement a custom route configuration
 */
class ConfigParser implements RouteConfigParserInterface
{
    public function parse(
        array $data,
        Route $route,
        ContainerInterface $container = null,
        string $file=null
    ): void
    {
        if(!array_key_exists('customConfig', $data)){
            return;
        }

        $route->getConfig()->getPreDispatchMiddleware()->append(new PreDispatch());
        $route->getConfig()->getPostDispatchMiddleware()->append(new PostDispatch());
    }
}

$exceptionHandlerCollection = new ExceptionHandlerCollection();
$exceptionHandlerCollection->append(new HttpMethodNotAllowedExceptionHandler());
$exceptionHandlerCollection->append(new HttpRouteNotFoundExceptionHandler());
$exceptionHandlerCollection->append(new InvalidContentTypeExceptionHandler());

$parserCollection = new RouteConfigParserCollection();
$parserCollection->append(new ConfigParser());

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response,
    $exceptionHandlerCollection
);

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router,
    null,
    $parserCollection
);

$group = new RouteGroup('Test Group', 'test', $routes);

$router->addGroup($group);

$router->dispatch()->send();
