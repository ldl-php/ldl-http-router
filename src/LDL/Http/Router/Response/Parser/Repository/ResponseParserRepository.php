<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Repository;

use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Exception\TypeMismatchException;

class ResponseParserRepository extends ObjectCollection
{
    private $last;

    /**
     * @param ResponseParserInterface $item
     * @param string|int|null $key
     * @return Interfaces\CollectionInterface
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        $name = strtolower(implode('.', [$item->getNamespace(), $item->getName()]));
        $this->last = $name;
        return parent::append($item, $name);
    }

    public function getLast() : ResponseParserInterface
    {
        if(null === $this->last){
            throw new \RuntimeException('No response parsers were added to this repository');
        }

        return $this->offsetGet($this->last);
    }

    /**
     * @param $item
     * @throws TypeMismatchException
     */
    public function validateItem($item): void
    {
        parent::validateItem($item);

        if($item instanceof ResponseParserInterface){
            return;
        }

        $msg = sprintf(
            '"%s" expects an instance of "%s", instance of "%s" was given',
            __CLASS__,
            ResponseParserInterface::class,
            get_class($item)
        );

        throw new TypeMismatchException($msg);
    }
}