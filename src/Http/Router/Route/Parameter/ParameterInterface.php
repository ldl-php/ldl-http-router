<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

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
     * @param mixed $value
     * @return mixed
     */
    public function getTransformedValue($value);

    /**
     * @return bool
     */
    public function isRequired() : bool;

}