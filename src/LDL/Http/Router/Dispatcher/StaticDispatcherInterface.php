<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

interface StaticDispatcherInterface
{
    public function getStaticResult() : string;
}