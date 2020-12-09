<?php declare(strict_types=1);
/**
 * In some special occasions you might want to state that the result of a certain dispatcher is always static
 * This interface is applied in ldl-http-router-cache to provide means to have a placeholder which will be replaced
 * by the real dispatch result, when a dispatcher implements NoCacheInterface.
 */

namespace LDL\Http\Router\Dispatcher;

interface StaticDispatcherInterface
{
    public function getStaticResult() : string;
}