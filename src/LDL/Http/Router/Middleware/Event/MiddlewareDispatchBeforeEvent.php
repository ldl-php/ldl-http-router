<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Event;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultItemInterface;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigInterface;
use LDL\Http\Router\Middleware\MiddlewareInterface;
use League\Event\HasEventName;

class MiddlewareDispatchBeforeEvent implements HasEventName
{
    use NameableTrait;

    /**
     * @var MiddlewareInterface
     */
    private $dispatcher;

    /**
     * @var MiddlewareConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $arguments;

    public function __construct(
        string $name,
        MiddlewareInterface $dispatcher,
        MiddlewareConfigInterface $config,
        array $arguments
    )
    {
        $this->_tName = $name;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->arguments = $arguments;
    }

    public function eventName(): string
    {
        return $this->_tName;
    }

    public function getConfig() : MiddlewareConfigInterface
    {
        return $this->config;
    }

    public function getDispatcher() : MiddlewareInterface
    {
        return $this->dispatcher;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }
}