<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Result\Collection;

use LDL\Router\Core\Route\Collector\CollectedRouteInterface;
use LDL\Router\Core\Route\Dispatcher\Result\RouteDispatcherResultInterface;
use LDL\Type\Collection\AbstractTypedCollection;
use LDL\Validators\InterfaceComplianceValidator;

class RouteDispatcherResultCollection extends AbstractTypedCollection implements RouteDispatcherResultCollectionInterface
{
    /**
     * @var CollectedRouteInterface
     */
    private $cr;

    public function __construct(CollectedRouteInterface $cr, iterable $items = null)
    {
        $this->cr = $cr;

        $this->getAppendValueValidatorChain()
            ->getChainItems()
            ->append(new InterfaceComplianceValidator(RouteDispatcherResultInterface::class))
            ->lock();

        parent::__construct($items);
    }

    public function getCollectedRoute(): CollectedRouteInterface
    {
        return $this->cr;
    }

    public function findByDispatcherName(string $name): ?RouteDispatcherResultInterface
    {
        /**
         * @var RouteDispatcherResultInterface $result
         */
        foreach ($this as $result) {
            if ($result->getDispatcher()->getName() === $name) {
                return $result;
            }
        }

        return null;
    }

    public function getArray(): array
    {
        $return = [];
        /**
         * @var RouteDispatcherResultInterface $result
         */
        foreach ($this as $result) {
            $return[] = [
              'dispatcher' => $result->getDispatcher()->getName(),
              'result' => $result->getDispatcherResult(),
            ];
        }

        return $return;
    }
}
