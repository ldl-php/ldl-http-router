<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Path\Result;

class RoutePathMatchingResult implements RoutePathMatchingResultInterface
{
    /**
     * @var RoutePathParsingResultInterface
     */
    private $result;

    /**
     * @var array|null
     */
    private $parameters;

    public function __construct(
        RoutePathParsingResultInterface $result,
        ?array $parameters
    ) {
        $this->result = $result;
        $this->parameters = $parameters;
    }

    public function getResult(): RoutePathParsingResultInterface
    {
        return $this->result;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}
