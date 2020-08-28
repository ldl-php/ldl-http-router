<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Middleware;

use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class MiddlewareCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if ($item instanceof MiddlewareInterface) {
            return;
        }

        $msg = sprintf(
            '"%s" item must be an instance of "%s"',
            __CLASS__,
            MiddlewareInterface::class
        );

        throw new TypeMismatchException($msg);
    }

    /**
     * @param MiddlewareInterface $item
     * @param null                $key
     */
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
             * @var MiddlewareInterface $a
             * @var MiddlewareInterface $b
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
}
