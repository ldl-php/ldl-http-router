<?php

use LDL\Framework\Base\Traits\DescribableInterfaceTrait;
use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;

class HttpDispatcherExample implements RouteDispatcherInterface
{
    use NameableTrait;
    use DescribableInterfaceTrait;

    /**
     * @var string
     */
    private $data;

    public function __construct(string $name, string $data)
    {
        $this->_tName = $name;
        $this->_tDescription = 'No description';
        $this->data = $data;
    }

    public function dispatch(string $name = null)
    {
        return $this->data;
    }
}
