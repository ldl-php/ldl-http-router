<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Factory;

use LDL\Http\Router\Helper\ClassOrContainer;
use LDL\Http\Router\Response\Parser\JsonResponseParser;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserCollection;
use LDL\Http\Router\Route\Config\Parser\RouteConfigParserInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Factory\Exception\SchemaException;
use LDL\Http\Router\Route\Group\RouteCollection;
use LDL\Http\Router\Route\Middleware\MiddlewareCollection;
use LDL\Http\Router\Route\Middleware\PostDispatchMiddlewareCollection;
use LDL\Http\Router\Route\Parameter\Parameter;
use LDL\Http\Router\Route\Parameter\ParameterCollection;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Schema\SchemaRepositoryInterface;
use Psr\Container\ContainerInterface;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

class RouteFactory
{
    private static $baseDirectory;
    private static $file;

    public static function fromJsonFile(
        string $file,
        ContainerInterface $container = null,
        SchemaRepositoryInterface $schemaRepo = null,
        RouteConfigParserCollection $parserCollection = null
    ): RouteCollection {
        if (!file_exists($file)) {
            $msg = "Schema file: \"$file\" was not found";
            throw new Exception\SchemaFileError($msg);
        }

        self::$file = $file;

        if (!is_readable($file)) {
            $msg = "Could not read schema file \"$file\", permission denied!";
            throw new Exception\SchemaFileError(self::exceptionMessage([$msg]));
        }

        self::$baseDirectory = dirname($file);

        return self::fromJson(file_get_contents($file), $container, $schemaRepo, $parserCollection);
    }

    public static function fromJson(
        string $json,
        ContainerInterface $container = null,
        SchemaRepositoryInterface $schemaRepo = null,
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
                $container,
                $schemaRepo,
                $parserCollection
            );
        } catch (\Exception $e) {
            throw new Exception\JsonParseException(self::exceptionMessage([$e->getMessage()]));
        }
    }

    public static function fromArray(
        array $data,
        ContainerInterface $container = null,
        SchemaRepositoryInterface $schemaRepo = null,
        RouteConfigParserCollection $parserCollection = null
    ): RouteCollection {
        $collection = new RouteCollection();

        foreach ($data['routes'] as $route) {
            if (!array_key_exists('request', $route)) {
                $msg = '"request" section not found in route definition';
                throw new Exception\SectionNotFoundException(self::exceptionMessage([$msg]));
            }

            if (!array_key_exists('response', $route)) {
                $msg = '"response" section not found in route definition';
                throw new Exception\SectionNotFoundException(self::exceptionMessage([$msg]));
            }

            if (!array_key_exists('url', $route)) {
                $msg = '"url" section not found in route definition';
                throw new Exception\SectionNotFoundException(self::exceptionMessage([$msg]));
            }

            $config = new RouteConfig(
                array_key_exists('method', $route['request']) ? $route['request']['method'] : '',
                array_key_exists('version', $route) ? $route['version'] : '',
                self::getUrlPrefix($route),
                array_key_exists('name', $route) ? $route['name'] : '',
                array_key_exists('description', $route) ? $route['description'] : '',
                self::getResponseParser($route),
                self::getDispatcher($route, $container),
                self::getParameters($route, $container, $schemaRepo),
                self::getUrlParameters($route, $container, $schemaRepo),
                self::getRequestHeaderSchema($route, $schemaRepo),
                self::getRequestBodySchema($route, $schemaRepo),
                self::getMiddleware($route, 'predispatch', $container),
                self::getPostDispatchMiddleware($route, 'postdispatch', $container)
            );

            $instance = new Route($config);

            if (null !== $parserCollection) {
                /**
                 * @var RouteConfigParserInterface $routeParser
                 */
                foreach ($parserCollection as $routeParser) {
                    $routeParser->parse($route, $instance, $container);
                }
            }

            $collection->append($instance);
        }

        return $collection;
    }

    /**
     * @param array $route
     * @return ResponseParserInterface
     * @throws SchemaException
     * @throws \LDL\Http\Router\Helper\Exception\ClassNotFoundException
     * @throws \LDL\Http\Router\Helper\Exception\SectionNotFoundException
     * @throws \LDL\Http\Router\Helper\Exception\UndefinedContainerException
     */
    private static function getResponseParser(array $route) : ResponseParserInterface
    {
        if(false === array_key_exists('parser', $route['response'])){
            return new JsonResponseParser();
        }

        $return = ClassOrContainer::get($route['response']['parser']);

        if($return instanceof ResponseParserInterface) {
            return $return;
        }

        $msg = sprintf(
            'Response parser must be an instance of interface "%s", instance of "%s" was given',
            ResponseParserInterface::class,
            get_class($return)
        );
        throw new Exception\SchemaException(self::exceptionMessage([$msg]));
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

    private static function getParameters(
        array $route,
        ContainerInterface $container = null,
        SchemaRepositoryInterface $schemaRepo = null
    ): ?ParameterCollection {
        if (false === array_key_exists('parameters', $route['request'])) {
            return null;
        }

        if (false === array_key_exists('schema', $route['request']['parameters'])) {
            return null;
        }

        $schema = self::getSchema(
            $route['request']['parameters']['schema'],
            'parameters',
            $schemaRepo
        );

        return self::parseSchema($schema, $container);
    }

    private static function getUrlParameters(
        array $route,
        ContainerInterface $container = null,
        SchemaRepositoryInterface $schemaRepo = null
    ) {
        if (false === array_key_exists('parameters', $route['url'])) {
            return null;
        }

        if (false === array_key_exists('schema', $route['url']['parameters'])) {
            return null;
        }

        $schema = self::getSchema(
            $route['url']['parameters']['schema'],
            'parameters',
            $schemaRepo
        );

        return self::parseSchema($schema, $container);
    }

    private static function parseSchema($schema, ContainerInterface $container = null)
    {
        $schema = json_decode(json_encode($schema), true);
        $parameters = [];

        foreach ($schema['properties'] as $name => &$values) {
            $parameter = new Parameter($name);

            if (!array_key_exists('LDL', $values)) {
                $parameters[] = $parameter;
                continue;
            }

            if (!array_key_exists('converter', $values['LDL'])) {
                $parameters[] = $parameter;
                continue;
            }

            $converter = $values['LDL']['converter'];

            $converterInstance = null;

            if (array_key_exists('class', $converter)) {
                $converterInstance = new $converter['class']();
            }

            if (array_key_exists('container', $converter)) {
                try {
                    $converterInstance = $container->get($converter['container']);
                } catch (\Exception $e) {
                    throw new Exception\ConverterNotFoundException(self::exceptionMessage([$e->getMessage()]));
                }
            }

            if (null === $converterInstance) {
                $msg = 'Converter must specify a class or a container service';
                throw new Exception\ConverterNotFoundException(self::exceptionMessage([$msg]));
            }

            $parameter->setConverter($converterInstance);

            $parameters[] = $parameter;

            unset($values['LDL']);
        }

        unset($values);

        try {
            $schema = Schema::import(json_decode(json_encode($schema), false));
        } catch (\Exception $e) {
            throw new Exception\SchemaException(self::exceptionMessage([$e->getMessage()]));
        }

        return new ParameterCollection($parameters, $schema);
    }

    private static function getRequestBodySchema(array $route, SchemaRepositoryInterface $schemaRepo = null): ?SchemaContract
    {
        if (!array_key_exists('body', $route['request'])) {
            return null;
        }

        if (!array_key_exists('schema', $route['request']['body'])) {
            return null;
        }

        return self::getSchema($route['request']['body']['schema'], 'body', $schemaRepo);
    }

    private static function getRequestHeaderSchema(array $route, SchemaRepositoryInterface $schemaRepo = null): ?SchemaContract
    {
        if (!array_key_exists('headers', $route['request'])) {
            return null;
        }

        if (!array_key_exists('schema', $route['request']['headers'])) {
            return null;
        }

        return self::getSchema($route['request']['headers']['schema'], 'headers', $schemaRepo);
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
    ): ?MiddlewareCollection {
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

        $collection = new MiddlewareCollection();

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

    private static function getPostDispatchMiddleware(
        array $route,
        string $middlewareType,
        ContainerInterface $container = null
    ): ?PostDispatchMiddlewareCollection {
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

        $collection = new PostDispatchMiddlewareCollection();

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

    private static function getSchema(
        $schema,
        string $section,
        SchemaRepositoryInterface $schemaRepo = null
    ): ?SchemaContract {
        if (!is_array($schema)) {
            $msg = "No schema specification, in section: \"$section\", must specify repository or inline";
            throw new Exception\SchemaException(self::exceptionMessage([$msg]));
        }

        $type = strtolower(key($schema));

        switch ($type) {
            case 'repository':
                if (null === $schemaRepo) {
                    $msg = "Schema repository specified but no repository was given, in section: $section";
                    throw new SchemaException(self::exceptionMessage([$msg]));
                }

                $schemaData = $schemaRepo->getSchema($schema['repository']);
                break;

            case 'inline':
                $schemaData = $schema['inline'];
                break;

            default:
                $msg = "Bad schema specification: \"$type\", in section: \"$section\", must specify repository or inline";
                throw new Exception\SchemaException(self::exceptionMessage([$msg]));
                break;
        }

        try {
            return Schema::import(json_decode(json_encode($schemaData), false));
        } catch (\Exception $e) {
            throw new Exception\SchemaException(self::exceptionMessage([$e->getMessage(), "In section: \"$section\""]));
        }
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
}
