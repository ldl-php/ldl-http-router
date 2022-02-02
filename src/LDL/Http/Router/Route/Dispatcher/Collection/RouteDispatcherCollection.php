<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Dispatcher\Collection;

use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
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

    public function dispatch(): array
    {
        $result = [];

        /**
         * @var RouteDispatcherInterface $dispatcher
         */
        foreach ($this as $dispatcher) {
            $result[] = $dispatcher->dispatch();
        }

        return $result;
    }
}
