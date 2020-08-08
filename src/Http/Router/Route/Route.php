<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Cache\RouteCacheManager;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Cache\CacheableInterface;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\Exception\InvalidParameterException;
use LDL\Http\Router\Route\Parameter\ParameterCollection;


use Swaggest\JsonSchema\Context;

class Route implements RouteInterface
{
    /**
     * @var RouteConfig
     */
    private $config;

    /**
     * @var RouteDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var RouterGuardCollection
     */
    private $guards;

    /**
     * @var ?ParameterCollection
     */
    private $parameters;

    /**
     * @var RouteCacheManager
     */
    private $cacheManager;

    /**
     * Route constructor.
     *
     * @param RouteConfig $config
     * @param RouteDispatcherInterface $dispatcher
     * @param ParameterCollection|null $parameters
     * @param RouterGuardCollection|null $guards
     * @param RouteCacheManager $cacheManager
     */
    public function __construct(
        RouteConfig $config,
        RouteDispatcherInterface $dispatcher,
        RouteCacheManager $cacheManager,
        ParameterCollection $parameters=null,
        RouterGuardCollection $guards=null
    )
    {
        $this->config = $config;
        $this->parameters = $parameters;
        $this->dispatcher = $dispatcher;
        $this->guards = $guards;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return RouteConfig
     */
    public function getConfig(): RouteConfig
    {
        return $this->config;
    }

    /**
     * @return RouteDispatcherInterface
     */
    public function getDispatcher(): RouteDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @return ParameterCollection|null
     */
    public function getParameters() : ?ParameterCollection
    {
        return $this->parameters;
    }

    public function dispatch(RequestInterface $request, ResponseInterface $response) : void
    {
        $requestParameters = (object)$request->getQuery()->all();

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_BEFORE);

        $schema = $this->parameters->getSchema() ?? $this->parameters->getParametersSchema();

        if(null !== $schema){
            try{
                $context = new Context();
                $context->tolerateStrings = true;
                $schema->in($requestParameters, $context);
            }catch(\Exception $e){
                $response->setStatusCode(ResponseInterface::HTTP_CODE_BAD_REQUEST);
                throw new InvalidParameterException($e->getMessage());
            }
        }

        $cacheHit = $this->cacheManager->has($this->dispatcher, $request, $response);

        if($cacheHit){
            return;
        }

        $result = $this->dispatcher->dispatch(
            $request,
            $response,
            $this->parameters
        );

        if(null !== $result){
            $response->setContent(
                $this->config->getContentType() === 'application/json' ? json_encode($result) : $result
            );
        }

        $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_AFTER);

        $this->cacheManager->store($this->dispatcher, $request, $response);
    }

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards(): ?RouterGuardCollection
    {
        return $this->guards;
    }

    // Private methods

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $guardType
     */
    private function applyGuards(
        RequestInterface $request,
        ResponseInterface $response,
        string $guardType
    ) : void
    {
        if(null === $this->guards){
            return;
        }

        /**
         * @var RouterGuardInterface $guard
         */
        foreach($this->guards->filterByType($guardType) as $guard){
            $guard->validate($request, $response, $this->parameters);
        }

    }

}