<?php

declare(strict_types=1);

namespace LDL\Router\Core\Result;

use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;

class RouterDispatchResult implements RouterDispatchResultInterface
{
    /**
     * @var RoutePathMatchingResultInterface
     */
    private $path;

    /**
     * @var mixed
     */
    private $result;

    public function __construct(
        RoutePathMatchingResultInterface $path,
        $result
    ) {
        $this->path = $path;
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getMatchedPath(): RoutePathMatchingResultInterface
    {
        return $this->path;
    }
}
