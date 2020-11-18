<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception;

interface ModifiesResponseInterface
{
    public function getContent() : ?array;
}