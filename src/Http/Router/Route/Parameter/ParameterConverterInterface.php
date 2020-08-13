<?php

namespace LDL\Http\Router\Route\Parameter;

interface ParameterConverterInterface
{
    /**
     * @param ParameterInterface $value
     * @return mixed
     */
    public function convert(ParameterInterface $value);
}