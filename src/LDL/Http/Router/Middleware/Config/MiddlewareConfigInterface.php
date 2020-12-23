<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware\Config;

use LDL\Framework\Base\Contracts\NameableInterface;

interface MiddlewareConfigInterface extends NameableInterface
{
    /**
     * The value returned by this method decides if the value returned by the dispatcher
     * must be stored in the response parameters to make it accessible or not to other dispatchers
     *
     * The value will be present in the response parameters as:
     *
     * "dispatcher->geName() => $dispatcher->dispatch()"
     *
     * Default config value must be (boolean) true
     *
     * @return bool
     */
    public function isPartOfResponseParameters() : bool;

    /**
     * Decides if the dispatch value must be used as part of the response chain, this means if it will be visible
     * by the client.
     *
     * The value will be present in the final response, depending heavily on the selected response formatter and parser
     *
     * @return bool
     */
    public function isPartOfResponse() : bool;

    /**
     * @return array
     */
    public function getParameters() : array;

    /**
     * If the dispatcher isBlocking and throws an exception when dispatched the exception will be rethrown
     * and the execution of following dispatchers will be aborted.
     *
     * If the dispatcher is pass and throws an exception when dispatched, the following elements
     * inside the dispatcher chain will be executed normally, the message of said exception will be set
     * as the result of said dispatcher in the ResultChain.
     *
     * @return bool
     */
    public function isBlocking() : bool;

}
