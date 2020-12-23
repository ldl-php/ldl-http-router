<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Result\Item;

use LDL\Http\Router\Middleware\MiddlewareInterface;

class ResponseResultItem implements ResponseResultItemInterface
{
    /**
     * @var MiddlewareInterface
     */
    private $middleware;

    /**
     * @var mixed
     */
    private $result;

    public function __construct(
        MiddlewareInterface $middleware,
        $result
    )
    {
        $this->middleware = $middleware;
        $this->result = $result;
    }

    public function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }

    public function getResult()
    {
        return $this->result;
    }
}