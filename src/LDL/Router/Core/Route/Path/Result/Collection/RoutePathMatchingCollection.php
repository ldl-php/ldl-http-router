<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Path\Result\Collection;

use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class RoutePathMatchingCollection extends AbstractTypedCollection implements RoutePathMatchingCollectionInterface
{
    public function __construct(iterable $items = null)
    {
        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RoutePathMatchingResultInterface::class))
            ->lock();
        parent::__construct($items);
    }

    public function filterStatic(): RoutePathMatchingCollectionInterface
    {
        $return = [];
        /**
         * @var RoutePathMatchingResultInterface $r
         */
        foreach ($this as $r) {
            if (!$r->getPath()->isDynamic()) {
                $return[] = $r;
            }
        }

        return new self($return);
    }

    public function filterDynamic(): RoutePathMatchingCollectionInterface
    {
        $return = [];
        /**
         * @var RoutePathMatchingResultInterface $r
         */
        foreach ($this as $r) {
            if ($r->getPath()->isDynamic()) {
                $return[] = $r;
            }
        }

        return new self($return);
    }
}
