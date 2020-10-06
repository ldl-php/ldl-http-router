<?php declare(strict_types=1);

/**
 * Holds a collection of different MiddlewareInterface objects
 */

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Contracts\IsActiveInterface;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Filter\FilterByActiveStateInterface;
use LDL\Type\Collection\Interfaces\Namespaceable\NamespaceableInterface;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValidatorChainInterface;

interface MiddlewareChainInterface extends CollectionInterface, HasValidatorChainInterface, NamespaceableInterface, PrioritySortingInterface, FilterByActiveStateInterface
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
