<?php

namespace LDL\HTTP\Router\Route\Parameter;

use LDL\Type\Collection\Types\Object\ObjectCollection;
use Swaggest\JsonSchema\Schema;

class ParameterCollection extends ObjectCollection
{
    /**
     * @var Schema|null
     */
    private $schema;

    /**
     * ParameterCollection constructor.
     * @param iterable|null $items
     * @param Schema|null $schema
     */

    public function __construct(
        iterable $items = null,
        Schema $schema=null
    )
    {
        parent::__construct($items);

        $this->schema = $schema;
    }

    public function getSchema() : ?Schema
    {
        return $this->schema;
    }

    public function validateItem($item): void
    {
        parent::validateItem($item);

        if($item instanceof ParameterInterface){
            return;
        }

        $msg = sprintf(
          '"%s" expected value must be of type "%s", "%s" was given',
          __CLASS__,
          ParameterInterface::class,
          get_class($item)
        );

        throw new Exception\InvalidParameterException($msg);
    }

}