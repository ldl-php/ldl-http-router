<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Factory;

use LDL\Http\Router\Route\Parameter\RouteParameter;
use LDL\Http\Router\Route\Parameter\RouteParameterInterface;
use LDL\Http\Router\Validator\Exception\ValidationTerminateException;
use LDL\Type\Collection\Types\String\StringCollection;

abstract class RouteParameterFactory implements RouteParameterFactoryInterface
{

    public static function fromArray(
        array $parameterConfig,
        string $defaultSource = null
    ) : RouteParameterInterface
    {
        $source = array_key_exists('source', $parameterConfig) ? $parameterConfig['source'] : null;

        if(null !== $defaultSource && null === $source){
            $source = $defaultSource;
        }

        $name = $parameterConfig['name'];

        $hasAliases = array_key_exists('aliases', $parameterConfig) && is_array($parameterConfig['aliases']);

        $aliases = new StringCollection($hasAliases ? $parameterConfig['aliases'] : null);

        $resolver = null;

        if(
            array_key_exists('resolver', $parameterConfig) &&
            is_string($parameterConfig['resolver'])
        ){
            $resolver = $parameterConfig['resolver'];
        }

        $defaultValue = array_key_exists('default', $parameterConfig) ? $parameterConfig['default'] : null;

        $value = null;

        if(
            array_key_exists('rename', $parameterConfig) &&
            is_string($parameterConfig['rename'])
            && $parameterConfig['rename'] !== ''
        ){
            $name = $parameterConfig['rename'];
        }

        return new RouteParameter(
            $source,
            $name,
            $resolver,
            $aliases
        );

    }

}
