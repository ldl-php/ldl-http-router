<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher;

use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;

class CallableDispatcher implements NeedsDispatchersInterface
{
    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function getName(): string
    {
        return 'callable';
    }

    public function dispatch(RouteDispatcherResultCollectionInterface $results = null, string ...$params)
    {
        $callable = $this->callable;

        return $callable($results, ...$params);
    }
}
