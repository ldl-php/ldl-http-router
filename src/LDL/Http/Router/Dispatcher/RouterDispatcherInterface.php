<?php declare(strict_types=1);

namespace LDL\Http\Router\Dispatcher;

use LDL\Http\Router\Container\RouterContainerInterface;

interface RouterDispatcherInterface{

    public function dispatch(
        RouterContainerInterface $sources,
        string $httpMethod,
        string $uri
    ) : void;

}