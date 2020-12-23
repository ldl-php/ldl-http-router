<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Chain\Result;

use LDL\Http\Router\Middleware\Config\MiddlewareConfigInterface;
use LDL\Http\Router\Middleware\MiddlewareInterface;

class MiddlewareChainResultItem implements MiddlewareChainResultItemInterface
{
    /**
     * @var MiddlewareInterface
     */
    private $dispatcher;

    /**
     * @var MiddlewareConfigInterface
     */
    private $config;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var string
     */
    private $context;

    public function __construct(
        string $context,
        MiddlewareInterface $dispatcher,
        MiddlewareConfigInterface $config,
        $result
    )
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->result = $result;
        $this->context = $context;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getDispatcher(): MiddlewareInterface
    {
        return $this->dispatcher;
    }

    public function getConfig() : MiddlewareConfigInterface
    {
        return $this->config;
    }

    public function isPartOfResponse() : bool
    {
        return $this->isPartOfResponse;
    }

    public function getContext() : string
    {
        return $this->context;
    }
}
