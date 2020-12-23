<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Event;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultItemInterface;
use League\Event\HasEventName;

class MiddlewareDispatchAfterEvent implements HasEventName
{
    use NameableTrait;

    /**
     * @var MiddlewareChainResultItemInterface
     */
    private $result;

    public function __construct(string $name, MiddlewareChainResultItemInterface $result)
    {
        $this->_tName = $name;
    }

    public function eventName(): string
    {
        return $this->_tName;
    }

    public function getResult() : MiddlewareChainResultItemInterface
    {
        return $this->result;
    }
}