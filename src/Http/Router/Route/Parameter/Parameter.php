<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

class Parameter implements ParameterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $calledConverter;

    /**
     * @var callable
     */
    private $converter;

    /**
     * @var mixed
     */
    private $convertedValue;

    /**
     * @var bool
     */
    private $isFrozen = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function freeze() : ParameterInterface
    {
        $this->isFrozen = true;
        return $this;
    }

    public function setConverter(ParameterConverterInterface $converter) : ParameterInterface
    {
        $this->checkFrozenState();
        $this->converter = $converter;
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setValue($value) : ParameterInterface
    {
        $this->checkFrozenState();
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getConvertedValue(bool $cache=true)
    {
        if($this->calledConverter && $cache){
            return $this->convertedValue;
        }

        if(null === $this->converter){
            $msg = sprintf(
              'Parameter with name: "%s", has no value converter specified',
                $this->name
            );

            throw new Exception\NoConverterSpecifiedException($msg);
        }

        $this->convertedValue = $this->converter->convert($this);
        $this->calledConverter = true;
        return $this->convertedValue;
    }

    private function checkFrozenState()
    {
        if(false === $this->isFrozen){
            return;
        }

        $msg = "Parameter named \"{$this->name}\" is frozen, state can not be changed";

        throw new Exception\FrozenParameterException($msg);
    }

}