<?php

namespace LDL\HTTP\Router\Route\Parameter;

use Swaggest\JsonSchema\Schema;

class Parameter implements ParameterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var bool
     */
    private $calledCallable;

    /**
     * @var callable
     */
    private $transformer;

    /**
     * @var mixed
     */
    private $transformedValue;

    public function __construct(
        string $name,
        ?string $value,
        string $description='',
        callable $transformer=null,
        Schema $schema = null
    )
    {
        $this->setName($name)
            ->setDescription($description)
            ->setSchema($schema)
            ->setValue($value)
            ->setTransformer($transformer);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSchema() : ?Schema
    {
        return $this->schema;
    }

    public function getValue() : ?string
    {
        return $this->value;
    }

    public function getTransformedValue()
    {
        if(null === $this->transformer){
            $msg = sprintf(
              'Parameter with name: "%s", has no value transformer specified',
                $this->name
            );

            throw new Exception\NoTransformerSpecifiedException($msg);
        }

        if($this->calledCallable){
            return $this->transformedValue;
        }

        return $this->transformedValue = ($this->transformer)($this->value);
    }

    // Private methods

    private function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    private function setDescription(string $description) : self
    {
        $this->description = $description;
        return $this;
    }

    private function setSchema(Schema $schema=null) : self
    {
        $this->schema = $schema;
        return $this;
    }

    private function setValue(?string $value) : self
    {
        $this->value = $value;
        return $this;
    }

    private function setTransformer(callable $transformer=null) : self
    {
        $this->transformer = $transformer;
        return $this;
    }


}