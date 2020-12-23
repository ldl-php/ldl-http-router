<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;

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
     * @var ?array
     */
    private $options;

    /**
     * @var bool
     */
    private $isFormatted = false;

    public function __construct(string $name, ?array $options = null)
    {
        $this->name = $name;
        $this->options = $options;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getOptions() : ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options) : ResponseFormatterInterface
    {
        $this->options = $options;
        return $this;
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
        MiddlewareChainResultInterface $result,
        bool $setFormatted=false
    ) : ResponseFormatterInterface
    {
        $this->isFormatted = $setFormatted;
        $this->result = $this->_format($result);

        return $this;
    }

    abstract protected function _format(
        MiddlewareChainResultInterface $collection
    ) : ?array;
}