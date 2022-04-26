<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response\Encoder;

use LDL\Router\Core\Route\Dispatcher\Result\Collection\RouteDispatcherResultCollectionInterface;
use LDL\Router\Core\Route\Dispatcher\Result\RouteDispatcherResultInterface;

class JsonHttpResponseEncoder implements HttpResponseEncoderInterface
{
    /**
     * @var bool
     */
    private $pretty;

    public function __construct(bool $pretty = false)
    {
        $this->pretty = $pretty;
    }

    public function encode(RouteDispatcherResultCollectionInterface $result): string
    {
        $return = [];

        /**
         * @var RouteDispatcherResultInterface $r
         */
        foreach ($result as $r) {
            $return[] = [$r->getDispatcher()->getName() => $r->getDispatcherResult()];
        }

        return json_encode(
            $return,
            $this->pretty ? (\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT) : \JSON_THROW_ON_ERROR
        );
    }
}
