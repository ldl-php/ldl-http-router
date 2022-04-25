<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher;

use LDL\Framework\Base\Traits\DescribableInterfaceTrait;
use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;

class CallableDispatcher implements NeedsDispatchersInterface
{
    use NameableTrait;
    use DescribableInterfaceTrait;

    /**
     * @var callable
     */
    private $callable;

    public function __construct(
        callable $callable,
        string $name = null,
        string $description = null
    ) {
        $this->callable = $callable;
        $this->_tName = $name ?? 'callable';
        $this->_tDescription = $description ?? 'Collection of anonymous functions which are called from top to bottom';
    }

    public function dispatch(RouteDispatcherResultCollectionInterface $results = null, string ...$params)
    {
        $callable = $this->callable;

        return $callable($results, ...$params);
    }
}
