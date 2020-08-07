<?php declare(strict_types=1);

namespace LDL\Http\Router\Route;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Cache\CacheableInterface;
use LDL\Http\Router\Route\Cache\Config\RouteCacheConfig;
use LDL\Http\Router\Route\Config\RouteConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\Exception\InvalidParameterException;
use LDL\Http\Router\Route\Parameter\ParameterCollection;

use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

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
     * @var CacheAdapterInterface
     */
    private $cacheAdapter;

    /**
     * @var RouteCacheConfig
     */
    private $cacheConfig;

    /**
     * Route constructor.
     *
     * @param RouteConfig $config
     * @param RouteDispatcherInterface $dispatcher
     * @param ParameterCollection|null $parameters
     * @param RouterGuardCollection|null $guards
     * @param CacheAdapterInterface|null $cacheAdapter
     * @param RouteCacheConfig|null $cacheConfig
     */
    public function __construct(
        RouteConfig $config,
        RouteDispatcherInterface $dispatcher,
        ParameterCollection $parameters=null,
        RouterGuardCollection $guards=null,
        CacheAdapterInterface $cacheAdapter = null,
        RouteCacheConfig $cacheConfig = null
    )
    {
        $this->config = $config;
        $this->parameters = $parameters;
        $this->dispatcher = $dispatcher;
        $this->cacheAdapter = $cacheAdapter;
        $this->cacheConfig = $cacheConfig;
        $this->guards = $guards;
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

    /**
     * @return RouteCacheConfig|null
     */
    public function getCacheConfig() : ?RouteCacheConfig
    {
        return $this->cacheConfig;
    }

    /**
     * @return CacheAdapterInterface|null
     */
    public function getCacheAdapter() : ?CacheAdapterInterface
    {
        return $this->cacheAdapter;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws InvalidParameterException
     * @throws \Swaggest\JsonSchema\Exception
     */
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

        $cache = $this->dispatchFromCache($request, $response);

        if($cache){
            $this->applyGuards($request, $response, RouterGuardInterface::VALIDATE_AFTER);
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
    }

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

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards(): ?RouterGuardCollection
    {
        return $this->guards;
    }

    private function dispatchFromCache(RequestInterface $request, ResponseInterface $response) : bool
    {
        if(null === $this->cacheAdapter){
            return false;
        }

        if(null === $this->cacheConfig){
            return false;
        }

        if(!$this->dispatcher instanceof CacheableInterface){
            return false;
        }

        /**
         * @var CacheableInterface $dispatcher
         */
        $dispatcher = $this->dispatcher;

        $cache = $this->cacheAdapter->getItem($dispatcher->getCacheKey($request));

        if(!$cache->isHit()) {
            $response->setExpires(
                \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $this->cacheConfig
                        ->getExpiresAt()
                        ->format('Y-m-d H:i:s')
                )
            );

            $this->dispatcher->dispatch($request, $response);

            $response->setContent($response->getContent());

            return true;
        }

        $this->dispatcher->dispatch(
            $request,
            $response,
            $this->parameters
        );

    }

}