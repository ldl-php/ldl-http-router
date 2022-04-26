<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response\Encoder;

use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;

interface HttpResponseEncoderInterface
{
    public function encode(RouteDispatcherResultCollectionInterface $result): string;
}
