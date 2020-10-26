<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Factory;

use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollectionInterface;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
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
        MiddlewareChainInterface $routeMiddlewareChain = null,
        RouteConfigParserCollection $parserCollection = null,
        ExceptionHandlerCollection $routeExceptionHandlers = null
    ): RouteCollection {
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
            $routeMiddlewareChain,
            $parserCollection,
            $routeExceptionHandlers
        );
    }

    public static function fromJson(
        string $json,
        Router $router,
        MiddlewareChainInterface $routeMiddlewareChain = null,
        RouteConfigParserCollection $parserCollection = null,
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
                $routeMiddlewareChain,
                $parserCollection,
                $routeExceptionHandlers
            );
        } catch (\Exception $e) {
            throw new Exception\JsonParseException(self::exceptionMessage([$e->getMessage()]));
        }
    }

    public static function fromArray(
        array $data,
        Router $router,
        MiddlewareChainInterface $routeMiddlewareChain = null,
        RouteConfigParserCollection $parserCollection = null,
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

            $parsers = null;

            if($parserCollection){
                $parsers = clone($parserCollection);

                $parsers->init(
                    $route,
                    self::$file
                );
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
                self::getDispatchers($route, $router->getDispatcherChain()),
                self::getResponseParser($route, $router),
                self::getMiddleware(
                    $route,
                    MiddlewareChainInterface::CONTEXT_PRE_DISPATCH,
                    $routeMiddlewareChain
                ),
                self::getMiddleware($route,
                    MiddlewareChainInterface::CONTEXT_POST_DISPATCH,
                    $routeMiddlewareChain
                ),
                self::getHandlerExceptionParser($route, $routeExceptionHandlers),
                $parsers
            );

            $collection->append(new Route($router, $config));
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

    private static function getDispatchers(
        array $route,
        MiddlewareChainInterface $dispatcherRepository
    ): MiddlewareChainInterface
    {
        if (!array_key_exists('dispatcher', $route)) {
            $msg = 'No dispatcher was specified';
            throw new Exception\DispatcherNotFoundException(self::exceptionMessage([$msg]));
        }

        /**
         * @var MiddlewareChainInterface $result
         */
        $result = $dispatcherRepository->filterByKeys(
            !is_array($route['dispatcher']) ? [$route['dispatcher']] : $route['dispatcher']
        );

        return $result;
    }

    private static function getMiddleware(
        array $route,
        string $middlewareType,
        MiddlewareChainInterface $chain = null
    ): ?MiddlewareChainInterface
    {
        if (!array_key_exists($middlewareType, $route)) {
            return null;
        }

        if(null === $chain){
            throw new \LogicException('No middleware chain was given');
        }

        /**
         * @var MiddlewareChainInterface $result
         */
        $result = $chain->filterByKeys(
            is_array($route[$middlewareType]) ? $route[$middlewareType] : [$route[$middlewareType]]
        );

        return $result;
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
