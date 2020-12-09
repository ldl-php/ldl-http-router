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
use LDL\Http\Router\Route\Validator\RequestValidatorChain;
use LDL\Http\Router\Router;

class RouteFactory
{
    private static $baseDirectory;
    private static $file;

    public static function fromJsonFile(
        string $file,
        Router $router,
        RequestValidatorChain $requestValidators = null,
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
            $requestValidators,
            $dispatcherRepository,
            $routeExceptionHandlers
        );
    }

    public static function fromJson(
        string $json,
        Router $router,
        RequestValidatorChain $requestValidators = null,
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
                $requestValidators,
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
        RequestValidatorChain $requestValidators = null,
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
                self::getResponseParserOptions($route),
                self::getResponseFormatter($route),
                self::getResponseFormatterOptions($route),
                $route,
                self::$file
            );

            $collection->append(new Route(
                $router,
                $config,
                self::getMiddleware(
                    $route,
                    'preDispatch',
                    $requestValidators,
                    $dispatcherRepository
                ),
                self::getMiddleware(
                    $route,
                    'dispatchers',
                    $requestValidators,
                    $dispatcherRepository
                ),
                self::getMiddleware(
                    $route,
                    'postDispatch',
                    $requestValidators,
                    $dispatcherRepository
                ),
                self::getHandlerExceptionParser($route, $routeExceptionHandlers)
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
        string $middlewareType,
        RequestValidatorChain $requestValidators = null,
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
            $list = [ 'list' => [$list]];
        }

        $chain = new MiddlewareChain(array_key_exists('name', $list) ? $list['name'] : null);

        if(!array_key_exists('list', $list)){
            return $chain;
        }

        self::parseDispatchers($list['list'], $chain, $dispatcherRepository, $requestValidators);

        return $chain;
    }

    private static function parseDispatchers(
        array $list,
        MiddlewareChainInterface $chain,
        DispatcherRepository $dispatcherRepository,
        RequestValidatorChain $requestValidators = null
    ) : void
    {
        foreach ($list as $key => $values) {
            if($key === 'dispatchers'){
                foreach($values as $item){
                    $chain->append($dispatcherRepository->offsetGet($item['dispatcher']));

                    if(false === array_key_exists('validators', $item)){
                        continue;
                    }

                    $validators = $item['validators'];

                    if(count($validators) === 0){
                        continue;
                    }

                    if(false === is_array($validators)){
                        $validators = [
                            'list' => [
                                ['name' => $validators]
                            ]
                        ];
                    }

                    if(null === $requestValidators){
                        throw new \LogicException('No request validator chain was given');
                    }

                    $defaultStrict = array_key_exists('strict', $validators) ? (bool) $validators['strict'] : true;

                    foreach($validators['list'] as $validator){

                        if(false === array_key_exists('name', $validator)){
                            continue;
                        }

                        $strict = array_key_exists('strict', $validator) ? (bool) $validator['strict'] : $defaultStrict;

                        $offsetValidator = $requestValidators->offsetGet($validator['name']);

                        $chain->getValidatorChain()->append($offsetValidator->getNewInstance($strict));
                    }
                }
                continue;
            }

            $isGroup = is_array($values);

            if($isGroup){

                if(is_int($key)){
                    self::parseDispatchers($values, $chain, $dispatcherRepository, $requestValidators);
                    continue;
                }

                $group = new MiddlewareChain($key);
                self::parseDispatchers($values, $group, $dispatcherRepository, $requestValidators);
                $chain->append($group);
                continue;
            }

            $chain->append($dispatcherRepository->offsetGet($values));
        }
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
