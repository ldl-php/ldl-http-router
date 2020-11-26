<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser;

use LDL\Http\Router\Router;

abstract class AbstractResponseParser implements ResponseParserInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $result;

    /**
     * @var bool
     */
    private $isParsed = false;

    /**
     * @var array
     */
    private $options;

    public function __construct(string $name, array $options=[])
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isParsed() : bool
    {
        return $this->isParsed;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setOptions(?array $options) : ResponseParserInterface
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions() : ?array
    {
        return $this->options;
    }

    final public function parse(Router $router, ?array $data): void
    {
        $this->isParsed = true;
        $this->result = $this->_parse($router, $data);
    }

    abstract protected function _parse(Router $router, ?array $data) : ?string;
}