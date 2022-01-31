<?php

declare(strict_types=1);

namespace LDL\Router\Core\Result;

use LDL\Router\Core\Route\Path\Result\RoutePathMatchingResultInterface;

interface RouterDispatchResultInterface
{
    public function getResult();

    public function getMatchedPath(): RoutePathMatchingResultInterface;
}
