<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Config;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface MiddlewareConfigRepositoryInterface extends CollectionInterface, HasValueValidatorChainInterface
{

    /**
     * @param string $name
     * @return MiddlewareConfigInterface
     */
    public function get(string $name) : MiddlewareConfigInterface;

}