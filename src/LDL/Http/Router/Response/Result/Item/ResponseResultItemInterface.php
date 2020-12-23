<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Result\Item;

use LDL\Http\Router\Middleware\MiddlewareInterface;

interface ResponseResultItemInterface
{

    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @return MiddlewareInterface
     */
    public function getMiddleware() : MiddlewareInterface;

}