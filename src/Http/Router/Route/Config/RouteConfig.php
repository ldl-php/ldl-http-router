<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config;

use LDL\Http\Core\Request\Helper\RequestHelper;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Cache\RouteCacheManager;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\ParameterCollection;

class RouteConfig implements \JsonSerializable
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $version;

    /**
     * @var RouterGuardCollection
     */
    private $guards;

    /**
     * @var ParameterCollection
     */
    private $parameters;

    /**
     * @var RouteCacheManager
     */
    private $cacheManager;

    /**
     * @var RouteDispatcherInterface
     */
    private $dispatcher;

    /**
     * RouteConfig constructor.
     * @param string $version
     * @param string $prefix
     * @param string $name
     * @param string $description
     * @param string $method
     * @param string $contentType
     * @param RouteDispatcherInterface $dispatcher
     * @param RouteCacheManager $cacheManager
     * @param ParameterCollection|null $parameters
     * @param RouterGuardCollection|null $guards
     * @throws Exception\InvalidHttpMethodException
     */
    public function __construct(
        string $version,
        string $prefix,
        string $name,
        string $description,
        string $method,
        string $contentType,
        RouteDispatcherInterface $dispatcher,
        RouteCacheManager $cacheManager=null,
        ParameterCollection $parameters=null,
        RouterGuardCollection $guards=null
    )
    {
        $this->setPrefix($prefix)
        ->setMethod($method)
        ->setContentType($contentType)
        ->setName($name)
        ->setDescription($description)
        ->setVersion($version)
        ->setDispatcher($dispatcher)
        ->setCacheManager($cacheManager)
        ->setParameters($parameters ?? new ParameterCollection())
        ->setGuards($guards ?? new RouterGuardCollection());
    }

    public function addGuard(RouterGuardInterface $guard) : self
    {
        $guards = $this->guards ?? new RouterGuardCollection();

        $guards->append($guard);

        $this->guards = $guards;

        return $this;
    }

    /**
     * @param array $config
     * @return RouteConfig
     * @throws Exception\InvalidHttpMethodException
     */
    public static function fromArray(array $config) : self
    {
        $merge = array_merge(get_class_vars(__CLASS__), $config);
        return new static(...$merge);
    }

    public function toArray() : array
    {
        return get_object_vars($this);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getDispatcher() : RouteDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @return string
     */
    public function getContentType() : string
    {
        return $this->contentType;
    }

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards() : ?RouterGuardCollection
    {
        return $this->guards;
    }

    /**
     * @return ParameterCollection|null
     */
    public function getParameters() : ?ParameterCollection
    {
        return $this->parameters;
    }

    public function getCacheManager() : ?RouteCacheManager
    {
        return $this->cacheManager;
    }

    //Private methods

    /**
     * Must be called after setPrefix
     *
     * @param string $method
     * @return RouteConfig
     * @throws Exception\InvalidHttpMethodException
     */
    private function setMethod(string $method) : self
    {
        if(RequestHelper::isHttpMethodValid(strtoupper($method))){
            $this->method = $method;
            return $this;
        }

        $msg = sprintf(
            'Invalid method specified: "%s" for route with prefix: "%s", valid methods are: "%s"',
            $method,
            $this->prefix,
            implode(', ', RequestHelper::getAvailableHttpMethods())
        );

        throw new Exception\InvalidHttpMethodException($msg);
    }

    private function setContentType(string $contentType) : self
    {
        $this->contentType = $contentType;
        return $this;
    }

    private function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    private function setPrefix(string $prefix) : self
    {
        $this->prefix = $prefix;
        return $this;
    }

    private function setDescription(string $description) : self
    {
        $this->description = $description;
        return $this;
    }

    private function setVersion(string $version) : self
    {
        $this->version = $version;
        return $this;
    }

    private function setCacheManager(RouteCacheManager $cacheManager=null) : self
    {
        $this->cacheManager = $cacheManager;
        return $this;
    }

    private function setDispatcher(RouteDispatcherInterface $dispatcher) : self
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    private function setParameters(ParameterCollection $parameterCollection=null) : self
    {
        $this->parameters = $parameterCollection;
        return $this;
    }

    private function setGuards(RouterGuardCollection $guards=null) : self
    {
        $this->guards = $guards;
        return $this;
    }

}