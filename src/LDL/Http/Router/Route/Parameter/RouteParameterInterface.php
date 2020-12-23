<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

use LDL\Type\Collection\Types\String\StringCollection;

interface RouteParameterInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * Checks if the parameter is named $name, or if an *alias* matches the given $name
     *
     * @see RouteParameterInterface::getAliases
     * @param string $name
     * @return bool
     */
    public function isNamed(string $name) : bool;

    /**
     * @return string
     */
    public function getSource() : string;

    /**
     * @return string
     */
    public function getResolver() : ?string;

    /**
     * Useful when you have two dispatchers which require exactly the same parameter, but the name is different
     *
     * @return StringCollection
     */
    public function getAliases() : StringCollection;
}
