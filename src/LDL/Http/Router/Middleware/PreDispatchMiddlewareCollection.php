<?php

declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Route;
use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class PreDispatchMiddlewareCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if ($item instanceof PreDispatchMiddlewareInterface) {
            return;
        }

        $msg = sprintf(
            '"%s" item must be an instance of "%s"',
            __CLASS__,
            PreDispatchMiddlewareInterface::class
        );

        throw new TypeMismatchException($msg);
    }

    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, $key ?? \spl_object_hash($item));
    }

    public function sort(string $order = 'asc'): self
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new \LogicException('Order must be one of "asc" or "desc"');
        }

        $items = \iterator_to_array($this);

        usort(
            $items,
            /**
             * @var PreDispatchMiddlewareInterface $a
             * @var PreDispatchMiddlewareInterface $b
             *
             * @return bool
             */
            static function ($a, $b) use ($order) {
                $prioA = $a->getPriority();
                $prioB = $b->getPriority();

                return 'asc' === $order ? $prioA <=> $prioB : $prioB <=> $prioA;
            }
        );

        return new static($items);
    }

    public function dispatch(
        Route $route,
        RequestInterface $request,
        ResponseInterface $response,
        array $urlArgs = []
    ) : array
    {
        $result = [];

        /**
         * @var PreDispatchMiddlewareInterface $preDispatch
         */
        foreach ($this->sort('asc') as $preDispatch) {
            if (false === $preDispatch->isActive()) {
                continue;
            }

            $preResult = $preDispatch->dispatch(
                $route,
                $request,
                $response,
                $urlArgs
            );

            if(null !== $preResult){
                $result[$preDispatch->getNamespace()] = [
                    $preDispatch->getName() => $preResult
                ];
            }

            $httpStatusCode = $response->getStatusCode();

            if ($httpStatusCode !== ResponseInterface::HTTP_CODE_OK){
                break;
            }
        }

        return $result;
    }

}
