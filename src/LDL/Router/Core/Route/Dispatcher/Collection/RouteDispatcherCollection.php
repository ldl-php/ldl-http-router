<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Collection;

use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class RouteDispatcherCollection extends AbstractTypedCollection
{
    public function __construct(iterable $items = null)
    {
        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RouteDispatcherInterface::class))
            ->lock();

        parent::__construct($items);
    }

    public function dispatch(): iterable
    {
        /**
         * @var RouteDispatcherInterface $dispatcher
         */
        foreach ($this as $dispatcher) {
            yield new Result\RouteDispatcherCollectionResult(
                $dispatcher,
                $dispatcher->dispatch(...func_get_args())
            );
        }
    }
}
