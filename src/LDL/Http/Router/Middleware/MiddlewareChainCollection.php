<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Item\NamedItemCollection;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\ObjectCollectionInterface;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class MiddlewareChainCollection extends ObjectCollection
{

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);
        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(MiddlewareChainInterface::class))
            ->lock();
    }

    public function filterByClass(string $className): CollectionInterface
    {
        $namedCollection = new NamedItemCollection();

        /**
         * @var MiddlewareChainInterface $chain
         */
        foreach($this as $chain){
            $collection = $chain->filterByClass($className);

            if(count($collection) === 0){
                continue;
            }

            foreach($collection as $dispatcher){
                $namedCollection->append($dispatcher, $dispatcher->getName());
            }
        }

        return $namedCollection;
    }

    public function filterByClassRecursive(string $className, ObjectCollectionInterface $collection = null): CollectionInterface
    {
        $namedCollection = new NamedItemCollection();

        /**
         * @var MiddlewareChainInterface $chain
         */
        foreach($this as $chain){
            $collection = $chain->filterByClassRecursive($className, $collection);

            if(count($collection) === 0){
                continue;
            }

            foreach($collection as $dispatcher){
                $namedCollection->append($dispatcher, $dispatcher->getName());
            }
        }

        return $namedCollection;
    }

    public function filterByClasses(array $classes): CollectionInterface
    {
        $namedCollection = new NamedItemCollection();

        /**
         * @var MiddlewareChainInterface $chain
         */
        foreach($this as $chain){
            $collection = $chain->filterByClasses($classes);

            if(count($collection) === 0){
                continue;
            }

            foreach($collection as $dispatcher){
                $namedCollection->append($dispatcher, $dispatcher->getName());
            }
        }

        return $namedCollection;
    }

    public function filterByInterface(string $interface): CollectionInterface
    {
        $namedCollection = new NamedItemCollection();

        /**
         * @var MiddlewareChainInterface $chain
         */
        foreach($this as $chain){
            $collection = $chain->filterByInterface($interface);

            if(count($collection) === 0){
                continue;
            }

            foreach($collection as $dispatcher){
                $namedCollection->append($dispatcher, $dispatcher->getName());
            }
        }

        return $namedCollection;
    }
}
