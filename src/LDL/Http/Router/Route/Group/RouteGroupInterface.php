<?php

declare(strict_types=1);

namespace LDL\Http\Router\Route\Group;

interface RouteGroupInterface
{
    public function getPath(): string;
}
