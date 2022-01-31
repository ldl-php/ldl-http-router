<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Dispatcher\Collection\Result;

use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;

class RouteDispatcherCollectionResult implements RouteDispatcherCollectionResultInterface
{
    /**
     * @var RouteDispatcherInterface
     */
    private $dispatcher;

    private $result;

    public function __construct(
        RouteDispatcherInterface $dispatcher,
        $result
    ) {
        $this->dispatcher = $dispatcher;
        $this->result = $result;
    }

    public function getDispatcher(): RouteDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function getDispatcherResult()
    {
        return $this->result;
    }
}
