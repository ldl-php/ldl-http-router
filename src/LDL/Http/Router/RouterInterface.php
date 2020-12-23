<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Container\RouterContainerInterface;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Validator\Request\HasValidatorChainInterface;
use LDL\Type\Collection\Types\String\StringCollection;
use League\Event\EventDispatchingListenerRegistry;

interface RouterInterface extends HasValidatorChainInterface
{
    public function dispatch(): ResponseInterface;

    public function getCurrentRoute(): ?RouteInterface;

    public function getDispatcher(): RouterDispatcher;

    /**
     * @return RouterContainerInterface
     */
    public function getParameterSources() : RouterContainerInterface;

    /**
     * Contains a string list of dispatchers which will be used *before* the main dispatch action
     *
     * @return StringCollection
     */
    public function getPreDispatchList() : StringCollection;

    /**
     * Contains a string list of dispatchers which will be used *after* the main dispatch action
     *
     * @return StringCollection
     */
    public function getPostDispatchList() : StringCollection;

    /**
     * @return EventDispatchingListenerRegistry
     */
    public function getEventBus() : EventDispatchingListenerRegistry;

}
