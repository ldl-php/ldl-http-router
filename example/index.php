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
use LDL\Http\Router\Route\Cache\CacheableInterface;
use LDL\Http\Router\Route\Parameter\ParameterInterface;
use LDL\Http\Router\Route\Factory\RouteFactory;
use LDL\Http\Router\Route\Group\RouteGroup;
use LDL\Http\Router\Schema\SchemaRepository;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Route\Route;

class Dispatch implements RouteDispatcherInterface, CacheableInterface
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
        ParameterCollection $parameters = null
    ) : array
    {
        return [
            'converted' => $parameters->get('name')->getConvertedValue(),
            'date' => time(),
            'description' => 'This response will be cached for 10 seconds!'
        ];
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
    public function parse(array $data, Route $route): void
    {
        var_dump($data);
        die();
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
