<?php declare(strict_types=1);

require __DIR__.'/../../../../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;

use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Middleware\PreDispatchMiddlewareInterface;
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

/**
 * Class LocalPredispatch
 *
 * This predispatch only run on route testLocalPredispatchRoute
 */
class LocalPredispatch implements PreDispatchMiddlewareInterface
{
    public function getNamespace(): string
    {
        return 'LocalPreDispatchNamespace';
    }

    public function getName(): string
    {
        return 'LocalPreDispatch';
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
        return "This predispatch was execute only in testLocalPredispatchRoute Route";
    }
}

/**
 * Class PreDispatch
 *
 * This is going to be a GLOBAL predispatch, it means that will be execute in all routes
 */
class PreDispatch implements PreDispatchMiddlewareInterface
{
    public function getNamespace(): string
    {
        return 'PreDispatchNamespace';
    }

    public function getName(): string
    {
        return 'GlobalPreDispatch';
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
            'namesAreEquals' => $request->get('name') === $urlArgs['urlName']
        ];
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
        $route->getConfig()->getPreDispatchMiddleware()->append(new PreDispatch());
    }
}

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

$group = new RouteGroup('example', 'example', $routes);

$router->addGroup($group);

$router->dispatch()->send();
