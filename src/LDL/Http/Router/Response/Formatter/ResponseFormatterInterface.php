<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\MiddlewareChainCollection;
use LDL\Http\Router\Router;

interface ResponseFormatterInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * Parse all the results returned by the RouterDispatcher
     * @param Router $router
     * @param MiddlewareChainCollection $collection
     * @return mixed
     */
    public function format(
        Router $router,
        MiddlewareChainCollection $collection
    );
}