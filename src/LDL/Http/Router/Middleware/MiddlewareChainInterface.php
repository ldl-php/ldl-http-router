<?php declare(strict_types=1);

/**
 * Holds a collection of different MiddlewareInterface objects
 */

namespace LDL\Http\Router\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Filter\FilterByActiveStateInterface;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValidatorChainInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface MiddlewareChainInterface extends CollectionInterface, HasValidatorChainInterface, PrioritySortingInterface, FilterByActiveStateInterface, HasKeyValidatorChainInterface
{
    public const CONTEXT_PRE_DISPATCH = 'preDispatch';
    public const CONTEXT_POST_DISPATCH = 'postDispatch';
    /**
     * Obtains the last executed dispatcher in the middleware chain
     * will throw an exception if the chain was not yet dispatched
     *
     * @throws Exception\UndispatchedMiddlewareChainException
     * @return MiddlewareInterface
     */
    public function getLastExecutedDispatcher() : MiddlewareInterface;

    /**
     * Obtains the result of a dispatched chain, throws exception if the chain has not been dispatched
     * @throws Exception\UndispatchedMiddlewareChainException
     * @return array
     */
    public function getResult() : array;

    /**
     * Checks whether the chain has been dispatched or not
     * @return bool
     */
    public function isDispatched() : bool;

    /**
     * Dispatches the middleware chain which composes this collection
     *
     * @param RouteInterface $route
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ParameterBag $urlParameters
     *
     * @throws \Exception
     *
     * @return array
     */
    public function dispatch(
        RouteInterface $route,
        RequestInterface $request,
        ResponseInterface $response,
        ParameterBag $urlParameters=null
    ) : array;
}
