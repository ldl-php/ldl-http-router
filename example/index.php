<?php declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Http\Core\Request\Request;
use LDL\Http\Core\Request\RequestInterface;

use LDL\Http\Core\Response\Response;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Parameter\ParameterCollection;
use LDL\Http\Router\Route\Parameter\ParameterConverterInterface;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Router;
use LDL\Http\Router\Route\Parameter\ParameterInterface;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Schema\SchemaRepository;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\Middleware\MiddlewareInterface;
use LDL\Http\Router\Route\Middleware\PostDispatchMiddlewareInterface;
use Psr\Container\ContainerInterface;

class Dispatch implements RouteDispatcherInterface
{
    public function __construct()
    {
    }

    public function getCacheKey(RequestInterface $request): string
    {
        return 'test';
    }

    public function dispatch(
        RequestInterface $request,
        ResponseInterface $response,
        ParameterCollection $parameters = null,
        ParameterCollection $urlParameters = null
    )
    {
        return [
            'converted' => $parameters->get('name')->getConvertedValue()
        ];
    }
}

class PreDispatch implements MiddleWareInterface
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

    public function dispatch(Route $route, RequestInterface $request, ResponseInterface $response)
    {
        return 'pre dispatch result!';
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

    public function dispatch(Route $route, RequestInterface $request, ResponseInterface $response, array $array = [])
    {
        return 'post dispatch result!';
    }
}

class NameTransformer implements ParameterConverterInterface{
    public function convert(ParameterInterface $parameter)
    {
        return strtoupper($parameter->getValue());
    }
}

class ConfigParser implements RouteConfigParserInterface
{
    public function parse(array $data, Route $route, ContainerInterface $container = null): void
    {
        if(!array_key_exists('custom', $data)){
            return;
        }

        $route->getConfig()->getPreDispatchMiddleware()->append(new PreDispatch());
        $route->getConfig()->getPostDispatchMiddleware()->append(new PostDispatch());
    }
}

$parserCollection = new RouteConfigParserCollection();
$parserCollection->append(new ConfigParser());

$schemaRepo = new SchemaRepository();

$schemaRepo->append('./schema/header-schema.json', 'header-parameters.schema');
$schemaRepo->append('./schema/parameter-schema.json', 'request-parameters.schema');
$schemaRepo->append('./schema/url-parameters-schema.json', 'url-parameters.schema');

$routes = RouteFactory::fromJsonFile(
    './routes.json',
    null,
    $schemaRepo,
    $parserCollection
);

$group = new RouteGroup('student', 'student', $routes);

$response = new Response();

$router = new Router(
    Request::createFromGlobals(),
    $response
);

$router->addGroup($group);

$router->dispatch()->send();
