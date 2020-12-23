<?php declare(strict_types=1);

/**
 * Holds a collection of different MiddlewareInterface objects
 */

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Contracts\IsActiveInterface;
use LDL\Framework\Base\Contracts\LockableObjectInterface;
use LDL\Http\Router\Handler\Exception\Repository\ExceptionHandlerRepositoryInterface;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigRepositoryInterface;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepositoryInterface;
use LDL\Http\Router\Container\RouterContainerInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Filter\FilterByActiveStateInterface;
use LDL\Type\Collection\Interfaces\Selection\MultipleSelectionInterface;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

interface MiddlewareChainInterface extends CollectionInterface, HasValueValidatorChainInterface, PrioritySortingInterface, FilterByActiveStateInterface, HasKeyValidatorChainInterface,  MultipleSelectionInterface, IsActiveInterface, LockableObjectInterface
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

    /**
     * @param string $context
     * @param EventDispatcherInterface $events
     * @param RouterContainerInterface $sources
     * @param MiddlewareConfigRepositoryInterface $configRepository
     * @param ExceptionHandlerRepositoryInterface $exceptionHandlers
     * @return MiddlewareChainInterface
     */
    public function dispatch(
        string $context,
        EventDispatcherInterface $events,
        RouterContainerInterface $sources,
        MiddlewareConfigRepositoryInterface $configRepository,
        ExceptionHandlerRepositoryInterface $exceptionHandlers
    ) : MiddlewareChainInterface;
}
