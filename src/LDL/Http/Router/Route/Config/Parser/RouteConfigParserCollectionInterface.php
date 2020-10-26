<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValidatorChainInterface;

interface RouteConfigParserCollectionInterface extends CollectionInterface, HasValidatorChainInterface
{
    public function init(
        array $config,
        string $file = null
    ): RouteConfigParserCollection;

    /**
     * @param RouteInterface $route
     *
     * @throws \Exception
     */
    public function parse(RouteInterface $route) : void;
}