<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

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
    private $defaultValue;

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

    /**
     * @var bool
     */
    private $required;

    public function __construct(
        string $name,
        bool $required,
        string $defaultValue=null,
        string $description='',
        callable $transformer=null,
        Schema $schema = null
    )
    {
        $this->name = $name;
        $this->description = $description;
        $this->schema = $schema;
        $this->required = $required;
        $this->transformer = $transformer;
        $this->defaultValue = $defaultValue;
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

    public function getDefaultValue() : ?string
    {
        return $this->defaultValue;
    }

    public function getTransformedValue($value)
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

        return $this->transformedValue = ($this->transformer)($value);
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

}