<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

use LDL\Type\Collection\Types\Object\ObjectCollection;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\Structure\Composition;

class ParameterCollection extends ObjectCollection implements \JsonSerializable
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

    /**
     * @return Schema|null
     */
    public function getSchema() : ?Schema
    {
        return $this->schema;
    }

    /**
     * @return Schema|null
     * @throws \Swaggest\JsonSchema\Exception
     */
    public function getParametersSchema() : ?Schema
    {
        $schema = new \stdClass;
        $schema->type = "object";
        $schema->properties = new \stdClass;

        $required = [];

        /**
         * @var ParameterInterface $parameter
         */
        foreach($this as $parameter) {
            if(null === $parameter->getSchema()){
                continue;
            }

            if($parameter->isRequired()){
                $required[] = $parameter->getName();
            }

            $name = $parameter->getName();

            $schema->properties->$name = $parameter->getSchema();
        }

        foreach($required as $req){
            $schema->required[] = $req;
        }

        return Schema::import(json_decode(json_encode($schema)));

    }

    /**
     * @param string $name
     * @return mixed
     * @throws \LDL\Type\Collection\Exception\UndefinedOffsetException
     */
    public function get(string $name){
        return $this->offsetGet($name);
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

    public function toArray() : array
    {
        return \iterator_to_array($this);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

}