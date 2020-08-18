<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Factory;

use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Route\Cache\Config\RouteCacheConfig;
use LDL\Http\Router\Route\Cache\RouteCacheManager;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Factory\Exception\SchemaException;
use LDL\Http\Router\Route\Group\RouteCollection;
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

    public static function fromJsonFile(
        string $file,
        ContainerInterface $container=null,
        SchemaRepositoryInterface $schemaRepo = null
    ) : RouteCollection
    {
        self::$baseDirectory = dirname($file);

        if(!file_exists($file)){
            $msg = "Schema file: \"$file\" was not found";
            throw new Exception\SchemaFileError($msg);
        }

        if(!is_readable($file)){
            $msg = "Could not read schema file \"$file\", permission denied!";
            throw new Exception\SchemaFileError($msg);
        }

        return self::fromJson(file_get_contents($file), $container, $schemaRepo);
    }

    public static function fromJson(
        string $json,
        ContainerInterface $container=null,
        SchemaRepositoryInterface $schemaRepo = null
    ) : RouteCollection
    {
        return self::fromArray(json_decode($json, true), $container, $schemaRepo);
    }

    public static function fromArray(
        array $data,
        ContainerInterface $container=null,
        SchemaRepositoryInterface $schemaRepo=null
    ) : RouteCollection
    {
        $collection = new RouteCollection();

        foreach($data['routes'] as $route){

            if(!array_key_exists('request', $route)){
                $msg = "\"request\" section not found in route definition";
                throw new Exception\SectionNotFoundException($msg);
            }

            if(!array_key_exists('response', $route)){
                $msg = "\"response\" section not found in route definition";
                throw new Exception\SectionNotFoundException($msg);
            }

            $config = new RouteConfig(
                array_key_exists('method' , $route['request']) ? $route['request']['method'] : '',
                array_key_exists('version' , $route) ? $route['version'] : '',
                array_key_exists('prefix' , $route) ? $route['prefix'] : '',
                array_key_exists('name' , $route) ? $route['name'] : '',
                array_key_exists('description' , $route) ? $route['description'] : '',
                array_key_exists('contentType', $route['response']) ? $route['response']['contentType'] : '',
                self::getDispatcher($route, $container),
                self::getParameters($route, $container, $schemaRepo),
                self::getRequestHeaderSchema($route, $schemaRepo),
                self::getRequestBodySchema($route, $schemaRepo),
                self::getGuards($route, $container),
                self::getCacheManager($route, $container)
            );

            $collection->append(new Route($config));
        }

        return $collection;
    }

    private static function getCacheManager(array $route, ContainerInterface $container=null) : ?RouteCacheManager
    {
        if(!array_key_exists('cache', $route['response'])){
            return null;
        }

        if(!array_key_exists('config', $route['response']['cache'])){
            $msg = "config section not found for response cache";
            throw new Exception\SectionNotFoundException($msg);
        }

        if(!array_key_exists('adapter', $route['response']['cache'])){
            $msg = "adapter section not found for response cache";
            throw new Exception\SectionNotFoundException($msg);
        }

        $config = RouteCacheConfig::fromArray($route['response']['cache']['config']);
        $adapter = self::classOrContainer($route['response']['cache']['adapter']);

        return new RouteCacheManager(
            $adapter,
            $config
        );
    }

    private static function classOrContainer(array $data, ContainerInterface $container=null)
    {
        $hasClass = array_key_exists('class', $data);
        $hasContainer = array_key_exists('container', $data);

        if(!$hasClass && !$hasContainer){
            $msg = "class or container section not found";
            throw new Exception\SectionNotFoundException($msg);
        }

        if($hasContainer && $hasClass){
            $msg = "Must define class or container, can not define both";
            throw new Exception\SectionNotFoundException($msg);
        }

        if($hasClass){
            $className = $data['class'];

            if(!class_exists($className)){
                $msg = "Class \"$className\" not found";
                throw new Exception\ClassNotFoundException($msg);
            }

            $arguments = array_key_exists('arguments', $data) ? $data['arguments'] : [];

            return new $className(...array_values($arguments));
        }

        if(null === $container){
            $msg = 'Container section specified but no container was passed to this factory';
            throw new Exception\UndefinedContainerException($msg);
        }

        return $container->get($data['container']);
    }

    private static function getGuards(array $route, ContainerInterface $container=null) : ?RouterGuardCollection
    {
        if(!array_key_exists('guards', $route)){
            return null;
        }

        $guards = $route['guards'];
        $collection = new RouterGuardCollection();

        foreach($guards as $guard){
            $guardInstance = self::classOrContainer($guard);
            $collection->append($guardInstance);
        }

        return $collection;
    }

    private static function getParameters(
        array $route,
        ContainerInterface $container=null,
        SchemaRepositoryInterface $schemaRepo=null
    ) : ?ParameterCollection
    {
        if(false === array_key_exists('parameters', $route['request'])){
            return null;
        }

        if(false === array_key_exists('schema', $route['request']['parameters'])){
            return null;
        }

        $schema = self::getSchema(
            $route['request']['parameters']['schema'],
            'parameters',
            $schemaRepo
        );

        $schema = json_decode(json_encode($schema),true);
        $parameters = [];

        foreach($schema['properties'] as $name => &$values){
            $parameter = new Parameter($name);

            if(!array_key_exists('LDL', $values)){
                $parameters[] = $parameter;
                continue;
            }

            if(!array_key_exists('converter', $values['LDL'])){
                $parameters[] = $parameter;
                continue;
            }

            $converter = $values['LDL']['converter'];

            $converterInstance = null;

            if(array_key_exists('class', $converter)){
                $converterInstance = new $converter['class'];
            }

            if(array_key_exists('container', $converter)) {
                try {
                    $converterInstance = $container->get($converter['container']);
                }catch(\Exception $e){
                    throw new Exception\ConverterNotFoundException($e->getMessage());
                }
            }

            if(null === $converterInstance){
                throw new Exception\ConverterNotFoundException("Converter must specify a class or a container service");
            }

            $parameter->setConverter($converterInstance);

            $parameters[] = $parameter;

            unset($values['LDL']);
        }

        unset($values);

        try {

            $schema = Schema::import(json_decode(json_encode($schema), false));

        }catch(\Exception $e){

            throw new Exception\SchemaException($e->getMessage());

        }

        $collection = new ParameterCollection($parameters, $schema);

        return $collection;
    }

    private static function getRequestBodySchema(array $route, SchemaRepositoryInterface $schemaRepo=null) : ?SchemaContract
    {
        if(!array_key_exists('body', $route['request'])){
            return null;
        }

        if(!array_key_exists('schema', $route['request']['body'])){
            return null;
        }

        return self::getSchema($route['request']['body']['schema'], 'body', $schemaRepo);
    }

    private static function getRequestHeaderSchema(array $route, SchemaRepositoryInterface $schemaRepo=null) : ?SchemaContract
    {
        if(!array_key_exists('headers', $route['request'])){
            return null;
        }

        if(!array_key_exists('schema', $route['request']['headers'])){
            return null;
        }

        return self::getSchema($route['request']['headers']['schema'], 'headers', $schemaRepo);
    }

    private static function getDispatcher(array $route, ContainerInterface $container=null) : RouteDispatcherInterface
    {
        if(array_key_exists('class', $route['dispatcher'])){
            if(!class_exists($route['dispatcher']['class'])){
                $msg = "Could not find dispatcher class: \"{$route['dispatcher']['class']}\"";
                throw new Exception\DispatcherNotFoundException($msg);
            }

            return new $route['dispatcher']['class'];
        }

        if(array_key_exists('container', $route['dispatcher'])){
            try {
                return $container->get($route['dispatcher']['container']);
            }catch(\Exception $e){
                throw new Exception\DispatcherNotFoundException($e->getMessage());
            }
        }

        throw new Exception\DispatcherNotFoundException("No dispatcher was specified");
    }

    private static function getSchema(
        $schema,
        string $section,
        SchemaRepositoryInterface $schemaRepo=null
    ) : ?SchemaContract
    {

        if(!is_array($schema)){
            $msg = "No schema specification, in section: \"$section\", must specify repository or inline";
            throw new Exception\SchemaException($msg);
        }

        $type = strtolower(key($schema));

        switch($type) {
            case 'repository':
                if (null === $schemaRepo) {
                    $msg = "Schema repository specified but no repository was given, in section: $section";
                    throw new SchemaException($msg);
                }

                $schemaData = $schemaRepo->getSchema($schema['repository']);
                break;

            case 'inline':
                $schemaData = $schema['inline'];
                break;

            default:
                $msg = "Bad schema specification: \"$type\", in section: \"$section\", must specify repository or inline";
                throw new Exception\SchemaException($msg);
                break;
        }

        try {

            return Schema::import(json_decode(json_encode($schemaData), false));

        }catch(\Exception $e){

            throw new Exception\SchemaException("In section: \"$section\", {$e->getMessage()}");

        }

    }
}

