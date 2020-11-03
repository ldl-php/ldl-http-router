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
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;
use LDL\Http\Router\Handler\Exception\AbstractExceptionHandler;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Response\Parser\Repository\ResponseParserRepository;
use LDL\Http\Router\Middleware\AbstractMiddleware;
use LDL\Http\Router\Middleware\DispatcherRepository;

class Dispatcher extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $urlParams = null
    )
    {
        return [
            'result' => $urlParams->get('urlName')
        ];
    }
}

class Dispatcher2 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $urlParameters = null
    )
    {
        throw new \InvalidArgumentException('test');
    }
}

class Dispatcher3 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $urlParams = null
    )
    {
        return [
            'result' => $urlParams->get('urlName')
        ];
    }
}

class TestExceptionHandler extends AbstractExceptionHandler
{
    public function handle(
        Router $router,
        \Exception $e,
        ParameterBag $urlParameters = null
    ): ?int
    {
        if(!$e instanceof InvalidArgumentException){
            return null;
        }

        return ResponseInterface::HTTP_CODE_FORBIDDEN;
    }
}

class CustomDispatch1 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $parameterBag=null
    )
    {
        return ['result' => 'pre dispatch result!'];
    }
}

class CustomDispatch2 extends AbstractMiddleware
{
    public function _dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        RouteInterface $route = null,
        ParameterBag $parameterBag=null
    )
    {
        return ['result' => 'post dispatch result!'];
    }
}

/**
 * Class ConfigParser
 *
 * Useful for plugin developers to implement a custom route configuration
 */
class ConfigParser implements RouteConfigParserInterface
{
    public function parse(RouteInterface $route): void
    {
        $rawConfig = $route->getConfig()->getRawConfig();

        if(!array_key_exists('customConfig', $rawConfig)){
            return;
        }

        $test = new \LDL\Http\Router\Middleware\MiddlewareChain('myGroup');
        $test->setPriority(2);

        $test->append(new CustomDispatch1('one'))
            ->append(new CustomDispatch2('two'));

        $test2 = new \LDL\Http\Router\Middleware\MiddlewareChain('myGroup_2');
        $test2->setPriority(1);

        $test2->append(new CustomDispatch1('three'))
            ->append(new CustomDispatch2('four'));

        $route->getPreDispatchChain()->append($test);
        $route->getPreDispatchChain()->append($test2);
        $route->getPostDispatchChain()->append(new CustomDispatch2('custom_2'));
    }
}

$routerExceptionHandlers = new ExceptionHandlerCollection();

$routerExceptionHandlers->append(new HttpMethodNotAllowedExceptionHandler('http.method.not.allowed'))
->append(new HttpRouteNotFoundExceptionHandler('http.route.not.found'))
->append(new InvalidContentTypeExceptionHandler('http.invalid.content'));

$configParserRepository = new RouteConfigParserRepository();
$configParserRepository->append(new ConfigParser());

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response,
    $configParserRepository,
    $routerExceptionHandlers,
    new ResponseParserRepository()
);

$dispatcherRepository = new DispatcherRepository();

$dispatcherRepository->append(new Dispatcher('dispatcher'))
    ->append(new Dispatcher2('dispatcher2'))
    ->append(new Dispatcher3('dispatcher3'));

$routeExceptionHandlers = new ExceptionHandlerCollection();
$routeExceptionHandlers->append(new TestExceptionHandler('test.exception.handler'));

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    $router,
    $dispatcherRepository,
    $routeExceptionHandlers
);

$group = new RouteGroup('Test Group', 'test', $routes);

$router->addGroup($group);

$router->dispatch()->send();
