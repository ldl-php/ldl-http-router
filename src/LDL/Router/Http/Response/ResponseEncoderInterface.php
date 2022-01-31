<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response;

interface ResponseEncoderInterface
{
    public function encode(iterable $result): string;
}
