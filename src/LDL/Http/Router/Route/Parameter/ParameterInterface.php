<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

interface ParameterInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param $value
     * @return ParameterInterface
     */
    public function setValue($value) : ParameterInterface;

    /**
     * @param bool $cache
     * @return mixed
     */
    public function getConvertedValue(bool $cache=true);

    /**
     * @return mixed
     */
    public function getValue();

}