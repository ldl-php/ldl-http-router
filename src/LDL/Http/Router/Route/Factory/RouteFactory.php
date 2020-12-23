<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Factory;

use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Middleware\Config\MiddlewareConfig;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigRepository;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigRepositoryInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Group\RouteCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Types\String\StringCollection;

class RouteFactory
{
    private static $baseDirectory;
    private static $file;

    public static function fromJsonFile(
        string $file,
        Router $router
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
            $router
        );
    }

    public static function fromJson(
        string $json,
        Router $router
    ): RouteCollection {
        try {
            return self::fromArray(
                json_decode(
                    $json,
                    true,
                    2048,
                    \JSON_THROW_ON_ERROR
                ),
                $router
            );
        } catch (\Exception $e) {
            throw new Exception\JsonParseException(self::exceptionMessage([$e->getMessage()]));
        }
    }

    public static function fromArray(
        array $data,
        Router $router
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
                self::getMiddleware($route, 'preDispatch'),
                self::getMiddleware($route, 'dispatchers'),
                self::getMiddleware($route, 'postDispatch'),
                self::getRequestValidators($route),
                self::getResponseValidators($route),
                self::getRequestConfigurators($route),
                self::getExceptionHandlers($route),
                self::getResponseStatusCode($route),
                self::getRequestBodyParser($route),
                self::getResponseParser($route, $router),
                self::getResponseParserOptions($route),
                self::getResponseFormatter($route),
                self::getResponseFormatterOptions($route),
                $route,
                self::$file
            );

            $collection->append(new Route($router, $config));

        }

        return $collection;
    }

    private static function getRequestBodyParser(array $route) : ?string
    {
        if(isset($route['request']['body']['parser']) && is_string($route['request']['body']['parser'])){
            return $route['request']['body']['parser'];
        }

        return null;
    }

    private static function getResponseStatusCode(array $route) : ?int
    {
        if(isset($route['response']['success']) && is_int($route['response']['success'])){
            return $route['response']['success'];
        }

        return null;
    }

    private static function getRequestValidators(array $route) : StringCollection
    {
        $collection = new StringCollection();

        if(false === array_key_exists('request', $route)){
            return $collection;
        }

        if(false === array_key_exists('validators', $route['request'])){
            return $collection;
        }

        if(false === array_key_exists('list', $route['request']['validators'])){
            return $collection;
        }

        if(!is_array($route['request']['validators']['list'])){
            return $collection;
        }

        return $collection->appendMany($route['request']['validators']['list']);
    }

    private static function getResponseValidators(array $route) : StringCollection
    {
        $collection = new StringCollection();

        if(false === array_key_exists('response', $route)){
            return $collection;
        }

        if(false === array_key_exists('validators', $route['response'])){
            return $collection;
        }

        if(false === array_key_exists('list', $route['response']['validators'])){
            return $collection;
        }


        if(!is_array($route['response']['validators']['list'])){
            return $collection;
        }

        return $collection->appendMany($route['response']['validators']['list']);
    }

    private static function getRequestConfigurators(array $route) : StringCollection
    {
        $collection = new StringCollection();

        if(false === array_key_exists('request', $route)){
            return $collection;
        }

        if(false === array_key_exists('configurators', $route['request'])){
            return $collection;
        }


        if(false === array_key_exists('list', $route['request']['configurators'])){
            return $collection;
        }

        if(!is_array($route['request']['configurators']['list'])){
            return $collection;
        }

        return $collection->appendMany($route['request']['configurators']['list']);
    }

    private static function getResponseParser(array $route, Router $router) : ?string
    {
        if(false === array_key_exists('response', $route)){
            return null;
        }

        if(false === array_key_exists('parser', $route['response'])){
            return null;
        }

        if(false === array_key_exists('name', $route['response']['parser'])){
            return null;
        }

        $parserName = $route['response']['parser']['name'];

        if(!is_string($parserName)){
            $msg = sprintf(
                '"name" item in response "parser" section is expected to be a string, "%s" was given',
                gettype($parserName)
            );

            throw new Exception\SchemaException($msg);
        }

        return $route['response']['parser']['name'];
    }

    private static function getResponseParserOptions(array $route) : ?array
    {
        if(false === array_key_exists('response', $route)){
            return null;
        }

        if(false === array_key_exists('parser', $route['response'])){
            return null;
        }

        if(false === array_key_exists('options', $route['response']['parser'])){
            return null;
        }

        if(!is_array($route['response']['parser']['options'])){
            $msg = sprintf(
                '"options" item in response "parser" section is expected to be an array, "%s" was given',
                gettype($route['response']['parser']['options'])
            );

            throw new Exception\SchemaException($msg);
        }

        return $route['response']['parser']['options'];
    }

    private static function getResponseFormatter(array $route) : ?string
    {
        if(false === array_key_exists('response', $route)){
            return null;
        }

        if(false === array_key_exists('formatter', $route['response'])){
            return null;
        }

        if(false === array_key_exists('name', $route['response']['formatter'])){
            return null;
        }

        $formatterName = $route['response']['formatter']['name'];

        if(!is_string($formatterName)){
            $msg = sprintf(
                '"name" item in response "formatter" section is expected to be a string, "%s" was given',
                gettype($formatterName)
            );

            throw new Exception\SchemaException($msg);
        }

        return $route['response']['formatter']['name'];
    }

    private static function getResponseFormatterOptions(array $route) : ?array
    {
        if(false === array_key_exists('response', $route)){
            return null;
        }

        if(false === array_key_exists('formatter', $route['response'])){
            return null;
        }

        if(false === array_key_exists('options', $route['response']['formatter'])){
            return null;
        }

        if(!is_array($route['response']['formatter']['options'])){
            $msg = sprintf(
                '"options" item in "formatter" response section is expected to be an array, "%s" was given',
                gettype($route['response']['parser']['options'])
            );

            throw new Exception\SchemaException($msg);
        }

        return $route['response']['formatter']['options'];
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
        string $middlewareType
    ): MiddlewareConfigRepositoryInterface
    {
        $collection = new MiddlewareConfigRepository();

        if(!array_key_exists('middleware', $route)){
            return $collection;
        }

        if (!array_key_exists($middlewareType, $route['middleware'])) {
            return $collection;
        }

        if(!array_key_exists('list', $route['middleware'][$middlewareType])){
            return $collection;
        }

        foreach($route['middleware'][$middlewareType]['list'] as $middleware){
            $collection->append(
                new MiddlewareConfig(
                    $middleware['name'],
                    array_key_exists('parameters', $middleware) ? $middleware['parameters'] : [],
                    array_key_exists('store', $middleware) ? $middleware['store'] : true,
                    array_key_exists('response', $middleware) ? $middleware['response'] : true,
                    array_key_exists('block', $middleware) ? $middleware['block'] : true
                )
            );
        }

        return $collection;
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

    private static function getExceptionHandlers(array $route) : StringCollection
    {
        $collection = new StringCollection();

        if(!isset($route['response']['exception']['handlers'])) {
            return $collection;
        }

        if(!is_array($route['response']['exception']['handlers'])){
            $msg = 'response -> exception -> handlers must be an array';
            throw new Exception\InvalidSectionException(self::exceptionMessage([$msg]));
        }

        $collection->appendMany($route['response']['exception']['handlers']);

        return $collection;
    }
}
