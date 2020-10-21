<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Factory;

use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Helper\ClassOrContainer;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Middleware\MiddlewareChainInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Group\RouteCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;
use Psr\Container\ContainerInterface;

class RouteFactory
{
    private static $baseDirectory;
    private static $file;

    public static function fromJsonFile(
        string $file,
        Router $router,
        ContainerInterface $container = null,
        RouteConfigParserCollection $parserCollection = null
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

        return self::fromJson(file_get_contents($file), $router, $container, $parserCollection);
    }

    public static function fromJson(
        string $json,
        Router $router,
        ContainerInterface $container = null,
        RouteConfigParserCollection $parserCollection = null
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
                $container,
                $parserCollection
            );
        } catch (\Exception $e) {
            throw new Exception\JsonParseException(self::exceptionMessage([$e->getMessage()]));
        }
    }

    public static function fromArray(
        array $data,
        Router $router,
        ContainerInterface $container = null,
        RouteConfigParserCollection $parserCollection = null
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
                    $container,
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
                self::getDispatcher($route, $container),
                self::getResponseParser($route, $router),
                self::getMiddleware($route, MiddlewareChainInterface::CONTEXT_PRE_DISPATCH, $container),
                self::getMiddleware($route, MiddlewareChainInterface::CONTEXT_POST_DISPATCH, $container),
                self::getHandlerExceptionParser($route),
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

    private static function getDispatcher(array $route, ContainerInterface $container = null): RouteDispatcherInterface
    {
        if (!array_key_exists('dispatcher', $route)) {
            $msg = 'No dispatcher was specified';
            throw new Exception\DispatcherNotFoundException(self::exceptionMessage([$msg]));
        }

        $object = ClassOrContainer::get($route['dispatcher'], $container);

        if (!$object instanceof RouteDispatcherInterface) {
            $msg = sprintf(
                'Dispatcher must be an instance of "%s"',
                RouteDispatcherInterface::class
            );

            throw new Exception\InvalidSectionException(self::exceptionMessage([$msg]));
        }

        return $object;
    }

    private static function getMiddleware(
        array $route,
        string $middlewareType,
        ContainerInterface $container = null
    ): ?MiddlewareChainInterface {
        if (!array_key_exists($middlewareType, $route)) {
            return null;
        }

        $middlewareList = $route[$middlewareType];

        if (!is_array($middlewareList)) {
            $msg = sprintf(
                'Section "%s" must be of type array, "%s" given.',
                $middlewareType,
                gettype($middlewareList)
            );

            throw new Exception\InvalidSectionException(self::exceptionMessage([$msg]));
        }

        $collection = new MiddlewareChain();

        foreach ($middlewareList as $dispatcher) {
            $instance = ClassOrContainer::get($dispatcher, $container);
            try {
                $collection->append($instance);
            } catch (\Exception $e) {
                throw new Exception\InvalidSectionException(self::exceptionMessage([$e->getMessage()]));
            }
        }

        return $collection;
    }

    private static function exceptionMessage(array $messages): string
    {
        if (null === self::$file) {
            return sprintf('%s', implode(', ', $messages));
        }

        return sprintf(
            'In file: "%s",%s',
            self::$file,
            implode(', ', $messages)
        );
    }

    private static function getHandlerExceptionParser(array $route) : ?ExceptionHandlerCollection
    {
        if(!isset($route['response']['exception']['handlers'])) {
            return null;
        }
        if(!is_array($route['response']['exception']['handlers'])){
            $msg = 'response -> exception -> handlers must be an array';
            throw new Exception\InvalidSectionException(self::exceptionMessage([$msg]));
        }

        $collection = new ExceptionHandlerCollection();

        foreach($route['response']['exception']['handlers'] as $handler){
            $instance = ClassOrContainer::get($handler);

            try {
                $collection->append($instance);
            } catch (\Exception $e) {
                throw new Exception\InvalidSectionException(self::exceptionMessage([$e->getMessage()]));
            }
        }

        return $collection;
    }
}
