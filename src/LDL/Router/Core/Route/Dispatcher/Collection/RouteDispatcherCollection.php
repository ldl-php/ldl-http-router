<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Collection;

use LDL\Framework\Base\Collection\Contracts\CollectionInterface;
use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Dispatcher\CallableDispatcher;
use LDL\Router\Core\Route\Dispatcher\NeedsDispatchersInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollection;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Dispatcher\Result\RouteDispatcherResult;
use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\Chain\OrValidatorChain;
use LDL\Validators\InterfaceComplianceValidator;

class RouteDispatcherCollection extends AbstractTypedCollection implements RouteDispatcherCollectionInterface
{
    public function __construct(iterable $items = null)
    {
        $this->getAppendValueValidatorChain(OrValidatorChain::class)
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RouteDispatcherInterface::class))
            ->lock();

        parent::__construct($items);
    }

    public function append($item, $key = null): CollectionInterface
    {
        if ($item instanceof \Closure) {
            $item = new CallableDispatcher($item);
        }

        return parent::append($item, $key);
    }

    public function dispatch(CollectedRouteInterface $cr, string ...$params): RouteDispatcherResultCollectionInterface
    {
        $return = new RouteDispatcherResultCollection($cr);
        /**
         * @var RouteDispatcherInterface $dispatcher
         */
        foreach ($this as $dispatcher) {
            $return->append(
              new RouteDispatcherResult(
                  $dispatcher,
                  $dispatcher instanceof NeedsDispatchersInterface ? $dispatcher->dispatch($return, ...$params) : $dispatcher->dispatch(...$params)
              )
            );
        }

        return $return;
    }
}
