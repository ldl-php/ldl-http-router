<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Factory;

use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollectionInterface;
use LDL\Http\Router\Middleware\DispatcherRepository;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Group\RouteCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;

class RouteFactory
{
    private static $baseDirectory;
    private static $file;

    public static function fromJsonFile(
        string $file,
        Router $router,
        DispatcherRepository $dispatcherRepository = null,
        ExceptionHandlerCollection $routeExceptionHandlers = null
    ): RouteCollection
    {
        if (!file_exists($file)) {
            $msg = "Route config file: \"$file\" was not found";
            throw new Exception\SchemaFileError($msg);
        }

        self::$file = $file;

        if (!is_readable($file)) {
            $msg = "Could not read route config file \"$file\", permission denied!";
            throw new Exception\SchemaFileError(self::exceptionMessage([$msg]));
        }

        self::$baseDirectory = dirname($file);

        return self::fromJson(
            file_get_contents($file),
            $router,
            $dispatcherRepository,
            $routeExceptionHandlers
        );
    }

    public static function fromJson(
        string $json,
        Router $router,
        DispatcherRepository $dispatcherRepository = null,
        ExceptionHandlerCollection $routeExceptionHandlers = null
    ): RouteCollection {
        try {
            return self::fromArray(
                json_decode(
                    $json,
                    true,
                    2048,
                    \JSON_THROW_ON_ERROR
                ),
                $router,
                $dispatcherRepository,
                $routeExceptionHandlers
            );
        } catch (\Exception $e) {
            throw new Exception\JsonParseException(self::exceptionMessage([$e->getMessage()]));
        }
    }

    public static function fromArray(
        array $data,
        Router $router,
        DispatcherRepository $dispatcherRepository = null,
        ExceptionHandlerCollection $routeExceptionHandlers = null
    ): RouteCollection {
        $collection = new RouteCollection();

        foreach ($data['routes'] as $route) {

            if (!array_key_exists('request', $route)) {
                $msg = '"request" section not found in route definition';
                throw new Exception\SectionNotFoundException(self::exceptionMessage([$msg]));
            }

            if (!array_key_exists('url', $route)) {
                $msg = '"url" section not found in route definition';
                throw new Exception\SectionNotFoundException(self::exceptionMessage([$msg]));
            }

            /**
             * Parse basic settings for the route, such as request url, method, version, etc
             * To avoid extra overhead, extra configuration parsers will be parse when the route is dispatched.
             *
             * @see RouterDispatcher
             */
            $config = new RouteConfig(
                array_key_exists('method', $route['request']) ? $route['request']['method'] : '',
                array_key_exists('version', $route) ? $route['version'] : '',
                self::getUrlPrefix($route),
                array_key_exists('name', $route) ? $route['name'] : '',
                array_key_exists('description', $route) ? $route['description'] : '',
                self::getResponseParser($route, $router),
                $route,
                self::$file
            );

            $collection->append(new Route(
                $router,
                $config,
                self::getMiddleware(
                    $route,
                    'preDispatch',
                    $dispatcherRepository
                ),
                self::getMiddleware(
                    $route,
                    'dispatchers',
                    $dispatcherRepository
                ),
                self::getMiddleware(
                    $route,
                    'postDispatch',
                    $dispatcherRepository
                ),
                self::getHandlerExceptionParser($route, $routeExceptionHandlers),
            ));
        }

        return $collection;
    }

    private static function getResponseParser(array $route, Router $router) : ?string
    {
        if(false === array_key_exists('response', $route)){
            return null;
        }

        if(false === array_key_exists('parser', $route['response'])){
            return null;
        }

        $parserName = $route['response']['parser'];

        if(!is_string($parserName)){
            $msg = sprintf(
                '"parser" item in response section is expected to be a string, "%s" was given',
                gettype($parserName)
            );

            throw new Exception\SchemaException($msg);
        }

        return $route['response']['parser'];
    }

    private static function getUrlPrefix(array $route): string
    {
        if (!array_key_exists('prefix', $route['url'])) {
            $msg = '"prefix" not found in url section';
            throw new Exception\SchemaException(self::exceptionMessage([$msg]));
        }

        if (!is_string($route['url']['prefix'])) {
            $msg = '"prefix" parameter must be a string, in url section';
            throw new Exception\SchemaException(self::exceptionMessage([$msg]));
        }

        return $route['url']['prefix'];
    }

    private static function getMiddleware(
        array $route,
        string $middlewareType,
        DispatcherRepository $dispatcherRepository = null
    ): ?MiddlewareChainInterface
    {
        if(!array_key_exists('middleware', $route)){
            return null;
        }

        if (!array_key_exists($middlewareType, $route['middleware'])) {
            return null;
        }

        if(null === $dispatcherRepository){
            throw new \LogicException('No middleware chain was given');
        }

        $list = $route['middleware'][$middlewareType];

        if(!is_array($list)){
            $list = [ $list ];
        }

        /**
         * @var DispatcherRepository $dispatchers
         */
        $dispatchers = $dispatcherRepository->filterByKeys(array_map('mb_strtolower', $list));
        $chain = new MiddlewareChain($middlewareType);

        foreach($dispatchers as $dispatcher){
            $chain->append($dispatcher);
        }

        return $chain;
    }

    private static function exceptionMessage(array $messages): string
    {
        if (null === self::$file) {
            return sprintf('%s', implode(', ', $messages));
        }

        return sprintf(
            'In file: "%s", %s',
            self::$file,
            implode(', ', $messages)
        );
    }

    private static function getHandlerExceptionParser(
        array $route,
        ExceptionHandlerCollection $collection = null
    ) : ?ExceptionHandlerCollectionInterface
    {
        if(!isset($route['response']['exception']['handlers'])) {
            return null;
        }

        if(0 === $collection->count()){
            return null;
        }

        if(!is_array($route['response']['exception']['handlers'])){
            $msg = 'response -> exception -> handlers must be an array';
            throw new Exception\InvalidSectionException(self::exceptionMessage([$msg]));
        }

        /**
         * @var ExceptionHandlerCollectionInterface $result
         */
        $result = $collection->filterByKeys($route['response']['exception']['handlers']);

        return $result;
    }
}
