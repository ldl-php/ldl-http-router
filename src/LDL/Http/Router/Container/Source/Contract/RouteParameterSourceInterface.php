<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Source\Contract;

use LDL\Framework\Base\Contracts\NameableInterface;

interface RouteParameterSourceInterface extends NameableInterface
{

    /**
     * @return string
     */
    public function getMethod() : string;

    /**
     * @return mixed
     */
    public function getObject();

}