<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Cache;

use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\Cache\Config\RouteCacheConfig;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

class RouteCacheManager
{
    /**
     * @var CacheAdapterInterface
     */
    private $adapter;

    /**
     * @var RouteCacheConfig
     */
    private $config;

    /**
     * @var bool
     */
    private $enabled;

    public function __construct(
        CacheAdapterInterface $adapter=null,
        RouteCacheConfig $config=null
    )
    {
        $this->adapter = $adapter;
        $this->config = $config;
    }

    private function isEnabled(RouteDispatcherInterface $dispatcher) : bool
    {
        if(null !== $this->enabled){
            return $this->enabled;
        }

        if(null === $this->adapter){
            return $this->enabled = false;
        }

        if(null === $this->config){
            return $this->enabled = false;
        }

        if(false === $this->config->isEnabled()){
            return $this->enabled = false;
        }

        if(!$dispatcher instanceof CacheableInterface){
            return $this->enabled = false;
        }

        $this->enabled = true;
        return true;
    }

    public function has(
        RouteDispatcherInterface $dispatcher,
        RequestInterface $request,
        ResponseInterface $response
    ) : bool
    {
        if(false === $this->isEnabled($dispatcher)){
            return false;
        }

        /**
         * @var CacheableInterface $_dispatcher
         */
        $_dispatcher = $dispatcher;

        $headers = $request->getHeaderBag();

        $providedCacheKey = $headers->get('X-HTTP-CACHE-SECRET');

        $key = $_dispatcher->getCacheKey($request);

        $now = new \DateTime('now');

        $item = $this->adapter->getItem($key);

        if(!$item->isHit()) {
            return false;
        }

        $isPurgeRequest = $headers->get('X-HTTP-METHOD-OVERRIDE') === RequestInterface::HTTP_METHOD_PURGE;

        if(
            $isPurgeRequest &&
            $this->config->getSecretKey() &&
            $this->config->getSecretKey() === $providedCacheKey
        ){
            $this->adapter->deleteItem($key);
        }

        if($isPurgeRequest && null === $this->config->getSecretKey()){
            $this->adapter->deleteItem($key);
        }

        $value = $item->get();

        if($now > $value['expires']){
            $this->adapter->deleteItem($item);
            return false;
        }

        $response->setExpires($value['expires']);
        $response->setContent($value['data']);

        return true;

    }

    public function store(
        RouteDispatcherInterface $dispatcher,
        RequestInterface $request,
        ResponseInterface $response
    ) : void
    {
        if(false === $this->isEnabled($dispatcher)){
            return;
        }

        $item = $this->adapter->getItem($dispatcher->getCacheKey($request));

        $expires = 0;

        if($this->config->getExpiresAt()){
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $expires = $now->add($this->config->getExpiresAt());
            $item->expiresAfter($this->config->getExpiresAt());
            $response->setExpires($expires);
        }

        $encode = ['expires' => $expires, 'data' => $response->getContent()];

        $item->set($encode);
        $this->adapter->save($item);
        $this->adapter->commit();

    }

}