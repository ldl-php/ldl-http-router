<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Source\Contract;

use LDL\Framework\Base\Contracts\NameableInterface;

interface RouteParameterStaticSourceInterface extends NameableInterface
{

    public function getValue();

}