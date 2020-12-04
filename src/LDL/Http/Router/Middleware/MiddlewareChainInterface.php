<?php declare(strict_types=1);

/**
 * Holds a collection of different MiddlewareInterface objects
 */

namespace LDL\Http\Router\Middleware;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Filter\FilterByActiveStateInterface;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface MiddlewareChainInterface extends CollectionInterface, HasValueValidatorChainInterface, PrioritySortingInterface, FilterByActiveStateInterface, HasKeyValidatorChainInterface, MiddlewareInterface
{
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
    public function getResult() : ?array;

    /**
     * Returns the last exception
     * @return \Exception|null
     */
    public function getLastException() : ?\Exception;

}
