<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response;

use LDL\Router\Core\Route\Dispatcher\Collection\Result\RouteDispatcherCollectionResult;

class JsonResponseEncoder implements ResponseEncoderInterface
{
    /**
     * @var bool
     */
    private $pretty;

    public function __construct(bool $pretty = false)
    {
        $this->pretty = $pretty;
    }

    public function encode(iterable $result): string
    {
        $return = [];

        /**
         * @var RouteDispatcherCollectionResult $r
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
