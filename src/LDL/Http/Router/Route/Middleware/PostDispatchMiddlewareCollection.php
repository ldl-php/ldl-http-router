<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Middleware;

use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class PostDispatchMiddlewareCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if ($item instanceof PostDispatchMiddlewareInterface) {
            return;
        }

        $msg = sprintf(
            '"%s" item must be an instance of "%s"',
            __CLASS__,
            PostDispatchMiddlewareInterface::class
        );

        throw new TypeMismatchException($msg);
    }

    /**
     * @param PostDispatchMiddlewareInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, \spl_object_hash($item));
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
             * @var PostDispatchMiddlewareInterface $a
             * @var PostDispatchMiddlewareInterface $b
             *
             * @return bool
             */
            function ($a, $b) use ($order) {
                $prioA = $a->getPriority();
                $prioB = $b->getPriority();

                return 'asc' === $order ? $prioA <=> $prioB : $prioB <=> $prioA;
            }
        );

        return new static($items);
    }
}
