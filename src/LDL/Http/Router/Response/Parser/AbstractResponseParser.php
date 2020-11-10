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

    public function __construct(string $name)
    {
        $this->name = $name;
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

    final public function parse(array $data, Router $router): void
    {
        $this->isParsed = true;
        $this->result = $this->_parse($data, $router);
    }

    abstract protected function _parse(array $data, Router $router) : ?string;
}