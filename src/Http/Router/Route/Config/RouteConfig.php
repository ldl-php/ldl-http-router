<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config;

use LDL\Http\Core\Request\Helper\RequestHelper;
use LDL\Http\Router\Guard\RouterGuardCollection;
use LDL\Http\Router\Guard\RouterGuardInterface;
use LDL\Http\Router\Route\Cache\RouteCacheManager;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Http\Router\Route\Parameter\ParameterCollection;
use Swaggest\JsonSchema\SchemaContract;
use Symfony\Component\String\UnicodeString;

class RouteConfig implements \JsonSerializable
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var SchemaContract
     */
    private $headerSchema;

    /**
     * @var SchemaContract
     */
    private $bodySchema;

    /**
     * @var string
     */
    private $requestMethod;

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
    private $version;

    /**
     * @var RouterGuardCollection
     */
    private $guards;

    /**
     * @var ParameterCollection
     */
    private $requestParameters;

    /**
     * @var RouteCacheManager
     */
    private $cacheManager;

    /**
     * @var RouteDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $responseContentType;

    public function __construct(
        string $method,
        string $version,
        string $prefix,
        string $name,
        string $description,
        string $responseContentType,
        RouteDispatcherInterface $dispatcher,
        ParameterCollection $requestParameters=null,
        SchemaContract $requestHeaderSchema = null,
        SchemaContract $bodySchema = null,
        RouterGuardCollection $guards=null,
        RouteCacheManager $cacheManager=null
    )
    {
        $this->setPrefix($prefix)
        ->setName($name)
        ->setVersion($version)
        ->setRequestMethod($method)
        ->setResponseContentType($responseContentType)
        ->setRequestHeaderSchema($requestHeaderSchema)
        ->setRequestBodySchema($bodySchema)
        ->setDescription($description)
        ->setDispatcher($dispatcher)
        ->setCacheManager($cacheManager)
        ->setParameters($requestParameters)
        ->setGuards($guards ?? new RouterGuardCollection());
    }

    public function addGuard(RouterGuardInterface $guard) : self
    {
        $guards = $this->guards ?? new RouterGuardCollection();

        $guards->append($guard);

        $this->guards = $guards;

        return $this;
    }

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

    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * @return RouteDispatcherInterface
     */
    public function getDispatcher() : RouteDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @return RouterGuardCollection|null
     */
    public function getGuards() : ?RouterGuardCollection
    {
        return $this->guards;
    }

    /**
     * @return string
     */
    public function getRequestMethod() : string
    {
        return $this->requestMethod;
    }

    /**
     * @return string
     */
    public function getResponseContentType() : string
    {
        return $this->responseContentType;
    }

    /**
     * @return SchemaContract|null
     */
    public function getHeaderSchema() : ?SchemaContract
    {
        return $this->headerSchema;
    }

    /**
     * @return ParameterCollection|null
     */
    public function getRequestParameters() : ?ParameterCollection
    {
        return $this->requestParameters;
    }

    /**
     * @return RouteCacheManager|null
     */
    public function getCacheManager() : ?RouteCacheManager
    {
        return $this->cacheManager;
    }

    /**
     * @return SchemaContract|null
     */
    public function getBodySchema() : ?SchemaContract
    {
        return $this->bodySchema;
    }

    //Private methods

    private function setName(string $name) : self
    {
        $name = new UnicodeString($name);
        $name = (string)$name->trim();

        if('' === $name){
            $msg = "Route name can not be empty";
            throw new Exception\InvalidRouteNameException($msg);
        }

        $this->name = $name;
        return $this;
    }

    private function setRequestBodySchema(SchemaContract $schema=null) : self
    {
        $this->bodySchema = $schema;
        return $this;
    }

    private function setRequestHeaderSchema(SchemaContract $schema=null) : self
    {
        $this->headerSchema = $schema;
        return $this;
    }

    private function setResponseContentType(string $type) : self
    {
        $this->responseContentType = $type;
        return $this;
    }

    private function setPrefix(string $prefix) : self
    {
        $prefix = new UnicodeString($prefix);
        $prefix = (string)$prefix->trim();

        if('' === $prefix){
            $msg = "Route prefix can not be empty";
            throw new Exception\InvalidRoutePrefixException($msg);
        }

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
        $version = trim($version);

        if('' === $version){
            $msg = "Route version can not be empty";
            throw new Exception\InvalidRoutePrefixException($msg);
        }

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
        $this->requestParameters = $parameterCollection;
        return $this;
    }

    private function setGuards(RouterGuardCollection $guards=null) : self
    {
        $this->guards = $guards;
        return $this;
    }

    /**
     * Must be called after setPrefix
     *
     * @param string $method
     * @return RouteConfig
     * @throws Exception\InvalidHttpMethodException
     */
    private function setRequestMethod(string $method) : self
    {
        if(RequestHelper::isHttpMethodValid(strtoupper($method))){
            $this->requestMethod = $method;
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
}