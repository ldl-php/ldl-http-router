<?php declare(strict_types=1);

namespace LDL\Http\Router;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Dispatcher\RouterDispatcher;
use LDL\Http\Router\Response\Formatter\ResponseFormatterInterface;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Http\Router\Route\Group\RouteGroupInterface;
use LDL\Http\Router\Container\RouterContainerInterface;
use LDL\Http\Router\Route\Route;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Http\Router\Validator\Request\Traits\RequestValidatorChainTrait;
use LDL\Http\Router\Validator\Response\Traits\ResponseValidatorChainTrait;
use LDL\Type\Collection\Types\String\StringCollection;
use League\Event\EventDispatcher;
use League\Event\EventDispatchingListenerRegistry;
use Phroute\Phroute\RouteCollector;

class Router implements RouterInterface
{
    use RequestValidatorChainTrait;
    use ResponseValidatorChainTrait;

    /**
     * @var RouteCollector
     */
    private $collector;

    /**
     * @var Route
     */
    private $currentRoute;

    /**
     * Contains a string list of pre dispatchers
     *
     * @var StringCollection
     */
    private $preDispatchList;

    /**
     * Contains a string list of pre dispatchers
     *
     * @var StringCollection
     */
    private $postDispatchList;

    /**
     * @var RouterDispatcher
     */
    private $dispatcher;

    /**
     * @var RouterContainerInterface
     */
    private $sources;

    /**
     * @var EventDispatchingListenerRegistry
     */
    private $events;

    public function __construct(
        RouterContainerInterface $parameterSources,
        EventDispatchingListenerRegistry $events=null,
        StringCollection $preDispatchersList = null,
        StringCollection $postDispatchersList = null
    )
    {
        $this->collector = new RouteCollector();
        $this->sources = $parameterSources;
        $this->preDispatchList = $preDispatchersList ?? new StringCollection();
        $this->postDispatchList = $postDispatchersList ?? new StringCollection();
        $this->dispatcher = new RouterDispatcher($this);
        $this->events = new EventDispatcher();
    }

    public function getEventBus() : EventDispatchingListenerRegistry
    {
        return $this->events;
    }

    /**
     * Must be part of router config
     *
     * @return StringCollection
     */
    public function getPreDispatchList() : StringCollection
    {
        return $this->preDispatchList;
    }

    /**
     * Must be part of router config
     *
     * @return StringCollection
     */
    public function getPostDispatchList() : StringCollection
    {
        return $this->postDispatchList;
    }

    /**
     * @param RouteInterface $route
     * @param RouteGroupInterface|null $group
     *
     * @return RouterInterface
     */
    public function addRoute(RouteInterface $route, RouteGroupInterface $group=null) : RouterInterface
    {
        $response = $this->sources->getResponse();

        $config = $route->getConfig();

        $path = "v{$config->getVersion()}/{$config->getPrefix()}";

        if(null !== $group){
            $path = "{$group->getPrefix()}/$path";
        }

        $this->collector->addRoute(strtoupper($config->getRequestMethod()), $path, $route);

        return $this;
    }

    public function addGroup(RouteGroupInterface $group) : self
    {
        foreach($group->getRoutes() as $r){
            $this->addRoute($r, $group);
        }

        return $this;
    }

    public function setCurrentRoute(Route $route) : self
    {
        $this->currentRoute = $route;
        return $this;
    }

    public function getCurrentRoute() : ?RouteInterface
    {
        return $this->currentRoute;
    }

    public function getDispatcher() : RouterDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @return RouteCollector
     */
    public function getRouteCollector(): RouteCollector
    {
        return $this->collector;
    }

    public function getParameterSources(): RouterContainerInterface
    {
        return $this->sources;
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    public function dispatch() : ResponseInterface
    {

        $request  = $this->sources->getRequest();
        $response = $this->sources->getResponse();

        $this->dispatcher->initializeRoutes($this->collector->getData());

        try {

            $this->dispatcher
                ->dispatch(
                    $this->sources,
                    $request->getMethod(),
                    parse_url($request->getRequestUri(), \PHP_URL_PATH)
                );

            $response->setStatusCode(
                $this->getCurrentRoute()
                    ->getConfig()
                    ->getResponseSuccessCode()
            );

            $result = $this->sources->getResponseResult();

            /**
             * @var ResponseFormatterInterface $formatter
             */
            $result = $this->sources
                ->getResponseFormatters()
                ->getSelectedItem()
                ->format($result)
                ->getResult();

        }catch(\Exception $e){

            $eHandlers = $this->sources->getExceptionHandlers()->handle($e);
            $response->setStatusCode($eHandlers->getResponseCode());
            $result = $eHandlers->getContent();

        }

        /**
         * We need to transform the result to a string, whether it's an error or not.
         * For this we make use of the response parser and response formatter
         */

        /**
         * @var ResponseParserInterface $parser
         */
        $parser = $this->sources
            ->getResponseParsers()
            ->getSelectedItem();

        if (false === $parser->isParsed()) {
            $parser->parse(
                $this,
                $result
            );
        }

        /**
         * Set the content type header according to the response parser
         */
        $response->getHeaderBag()->set('Content-Type', $parser->getContentType());

        $response->setContent($parser->getResult());

        return $response;
    }

}