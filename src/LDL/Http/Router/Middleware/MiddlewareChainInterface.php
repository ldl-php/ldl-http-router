<?php declare(strict_types=1);

/**
 * Holds a collection of different MiddlewareInterface objects
 */

namespace LDL\Http\Router\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;

interface MiddlewareChainInterface
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
     * Obtains a middleware by namespace and name, throws an exception if the middleware could not be found
     *
     * @param string $namespace
     * @param string $name
     * @throws Exception\MiddlewareNotFoundException
     * @return MiddlewareInterface
     */
    public function getMiddleware(string $namespace, string $name) : MiddlewareInterface;

    /**
     * Sorts the dispatcher collection according to the priority set in each dispatcher
     *
     * @see MiddlewareInterface
     * @param string $order
     * @return MiddlewareChainInterface
     */
    public function sort(string $order = 'asc'): MiddlewareChainInterface;

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
     * @param Route $route
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $urlArgs
     * @return array
     */
    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    ) : array;
}
