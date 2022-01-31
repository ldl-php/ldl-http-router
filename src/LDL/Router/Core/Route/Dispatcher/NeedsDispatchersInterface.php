<?php

/**
 * When you use this interface on a RouteDispatcher, the first argument passed to the dispatch method
 * will be the result of previous dispatched dispatchers.
 *
 * Example:
 *
 * We have these dispatchers:
 *
 * Dispatcher 1
 * Dispatcher 2
 * Dispatcher 3
 *
 * The problem is that we need the result of Dispatcher 1 and Dispatcher 2 passed to Dispatcher 3
 *
 * If you implement this interface in Dispatcher 3, the dispatch method will receive an instance of :
 * RouteDispatcherResultCollectionInterface
 *
 * As the first argument
 *
 * (Dispatcher3::dispatch(RouteDispatcherResultCollectionInterface $dispatched)
 */

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher;

use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;

interface NeedsDispatchersInterface extends RouteDispatcherInterface
{
    public function dispatch(
        RouteDispatcherResultCollectionInterface $results = null,
        string ...$params
    );
}
