<?php

namespace LDL\HTTP\Router\Route\Parameter;

use Swaggest\JsonSchema\Schema;

interface ParameterInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return string
     */
    public function getDescription() : string;

    /**
     * @return Schema|null
     */
    public function getSchema() : ?Schema;

    /**
     * @return mixed
     */
    public function getTransformedValue();

}