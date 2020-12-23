<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Router\Route\Body\Parser\RequestBodyParserRepositoryInterface;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepositoryInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Types\String\StringCollection;
use Symfony\Component\HttpFoundation\ParameterBag;

interface RouteParametersInterface extends CollectionInterface, HasKeyValidatorChainInterface
{
    /**
     * @param RouteParameterInterface $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface;

    /**
     * @param string $name
     * @return RouteParameterInterface
     * @throws Exception\UndefinedMiddlewareParameterException
     */
    public function getParameter(string $name) : RouteParameterInterface;

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool;

    /**
     * Checks if $parameters is inside of the collection, if one or more parameters are not found
     * then a StringCollection will be returned, in case of success, null will be returned.
     *
     * Must use self::has for consistency
     *
     * @see RouteParametersInterface
     * @param iterable $parameters
     * @return StringCollection|null
     */
    public function hasParameters(iterable $parameters) : ?StringCollection;

    /**
     * Gets a parameter by name
     *
     * @param string $name
     * @param bool $cache
     *
     * @throws Exception\UndefinedMiddlewareParameterException
     *
     * @return mixed
     */
    public function get(string $name, bool $cache=true);

    /**
     * Must use get method in this very same interface for consistency
     *
     * @see RouteParametersInterface::get(string $name)
     *
     * @param $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @return RouteParameterResolverRepositoryInterface
     */
    public function getResolvers() : RouteParameterResolverRepositoryInterface;
}