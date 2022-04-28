<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response\Encoder;

use LDL\Framework\Base\Contracts\DescribableInterface;
use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;

interface HttpResponseEncoderInterface extends NameableInterface, DescribableInterface
{
    public function encode(ResponseInterface $response, RouteDispatcherResultCollectionInterface $result): void;
}
