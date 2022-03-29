<?php

use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;

class HttpDispatcherExample implements RouteDispatcherInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $data;

    public function __construct(string $name, string $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function dispatch(string $name = null)
    {
        return $this->data;
    }
}
