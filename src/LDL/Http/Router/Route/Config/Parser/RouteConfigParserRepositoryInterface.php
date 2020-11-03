<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValidatorChainInterface;

interface RouteConfigParserRepositoryInterface extends CollectionInterface, HasValidatorChainInterface
{
    /**
     * @param RouteInterface $route
     *
     * @throws \Exception
     */
    public function parse(RouteInterface $route) : void;
}