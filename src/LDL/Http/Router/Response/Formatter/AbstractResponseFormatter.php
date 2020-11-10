<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\MiddlewareChainCollection;
use LDL\Http\Router\Router;

abstract class AbstractResponseFormatter implements ResponseFormatterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array|null
     */
    private $result;

    /**
     * @var bool
     */
    private $isFormatted = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function isFormatted(): bool
    {
        return $this->isFormatted;
    }

    final public function format(
        Router $router,
        MiddlewareChainCollection $collection
    ) : void
    {
        $this->isFormatted = true;
        $this->result = $this->_format($router, $collection);
    }

    abstract protected function _format(
        Router $router,
        MiddlewareChainCollection $collection
    ) : ?array;
}