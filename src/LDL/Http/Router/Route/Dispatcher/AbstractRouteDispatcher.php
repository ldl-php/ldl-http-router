<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Dispatcher;

abstract class AbstractRouteDispatcher implements RouteDispatcherInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name){
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

}
