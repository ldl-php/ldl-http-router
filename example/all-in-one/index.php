<?php declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

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
use LDL\Http\Router\Middleware\PreDispatchMiddlewareInterface;
use LDL\Http\Router\Middleware\PostDispatchMiddlewareInterface;
use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use Psr\Container\ContainerInterface;

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

class PreDispatch implements PreDispatchMiddlewareInterface
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
        /**
         * Do something before dispatch runs. For example, we want to know if the url name is
         * equal to the parameter given name
         */

        return [
            'urlName' => $urlArgs['urlName'],
            'name' => $request->get('name'),
            'namesAreEquals' => $request->get('name') === $urlArgs['urlName']
        ];
    }
}

class PostDispatch implements PostDispatchMiddlewareInterface
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
        array $array = []
    )
    {
        return "Do something after dispatch runs";
    }
}

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

class TestingExceptionHandler implements ExceptionHandlerInterface
{
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
        return null;
    }
}

/**
 * Add default ExceptionHandlers
 */
$exceptionHandlerCollection = new ExceptionHandlerCollection();
$exceptionHandlerCollection->append(new HttpMethodNotAllowedExceptionHandler());
$exceptionHandlerCollection->append(new HttpRouteNotFoundExceptionHandler());
$exceptionHandlerCollection->append(new InvalidContentTypeExceptionHandler());

$parserCollection = new RouteConfigParserCollection();
$parserCollection->append(new ConfigParser());

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response
);

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router,
    null,
    $parserCollection
);

$group = new RouteGroup('student', 'student', $routes);

$router->addGroup($group);

$router->dispatch()->send();
