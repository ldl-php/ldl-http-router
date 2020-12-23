<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Source\Contract;

interface RouteParameterSourceCallbackInterface extends RouteParameterSourceInterface
{

    public function getCallback() : callable;

}