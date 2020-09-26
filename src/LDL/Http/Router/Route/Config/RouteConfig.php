<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config;

use LDL\Http\Core\Request\Helper\RequestHelper;
use LDL\Http\Router\Handler\Exception\Collection\ExceptionHandlerCollection;
use LDL\Http\Router\Middleware\MiddlewareChain;
use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Http\Router\Route\Dispatcher\RouteDispatcherInterface;
use Symfony\Component\String\UnicodeString;

class RouteConfig implements \JsonSerializable
{
    /**
     * @var string
     */
    private $prefix;

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
     * @var RouteDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var MiddlewareChain
     */
    private $preDispatch;

    /**
     * @var MiddlewareChain
     */
    private $postDispatch;

    /**
     * @var ExceptionHandlerCollection
     */
    private $exceptionHandlerCollection;

    public function __construct(
        string $method,
        string $version,
        string $prefix,
        string $name,
        string $description,
        RouteDispatcherInterface $dispatcher,
        ResponseParserInterface $responseParser = null,
        MiddlewareChain $preDispatchMiddleware = null,
        MiddlewareChain $postDispatchMiddleware = null,
        ExceptionHandlerCollection $exceptionHandlerCollection = null
    )
    {
        $this->setPrefix($prefix)
        ->setName($name)
        ->setVersion($version)
        ->setRequestMethod($method)
        ->setResponseParser($responseParser)
        ->setDescription($description)
        ->setDispatcher($dispatcher)
        ->setPreDispatchMiddleware($preDispatchMiddleware ?? new MiddlewareChain())
        ->setPostDispatchMiddleware($postDispatchMiddleware ?? new MiddlewareChain())
        ->setExceptionHandlerCollection($exceptionHandlerCollection ?? new ExceptionHandlerCollection());
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
     * @return string
     */
    public function getRequestMethod() : string
    {
        return $this->requestMethod;
    }

    /**
     * @return ResponseParserInterface
     */
    public function getResponseParser() : ?ResponseParserInterface
    {
        return $this->responseParser;
    }

    /**
     * @return MiddlewareChain
     */
    public function getPreDispatchMiddleware() : MiddlewareChain
    {
        return $this->preDispatch;
    }

    /**
     * @return MiddlewareChain
     */
    public function getPostDispatchMiddleware() : MiddlewareChain
    {
        return $this->postDispatch;
    }

    /**
     * @return ExceptionHandlerCollection
     */
    public function getExceptionHandlerCollection(): ExceptionHandlerCollection
    {
        return $this->exceptionHandlerCollection;
    }

    //<editor-fold desc="Private methods">

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

    private function setResponseParser(ResponseParserInterface $parser=null) : self
    {
        $this->responseParser = $parser;
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

    private function setDispatcher(RouteDispatcherInterface $dispatcher) : self
    {
        $this->dispatcher = $dispatcher;
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

    /**
     * @param MiddlewareChain $chain
     * @return RouteConfig
     */
    private function setPreDispatchMiddleware(MiddlewareChain $chain) : self
    {
        $this->preDispatch = $chain;
        return $this;
    }

    /**
     * @param MiddlewareChain $chain
     * @return RouteConfig
     */
    private function setPostDispatchMiddleware(MiddlewareChain $chain) : self
    {
        $this->postDispatch = $chain;
        return $this;
    }

    /**
     * @param ExceptionHandlerCollection $exceptionHandlerCollection
     * @return RouteConfig
     */
    private function setExceptionHandlerCollection(ExceptionHandlerCollection $exceptionHandlerCollection): RouteConfig
    {
        $this->exceptionHandlerCollection = $exceptionHandlerCollection;
        return $this;
    }

    //</editor-fold>
}