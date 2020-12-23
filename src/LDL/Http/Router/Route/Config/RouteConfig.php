<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config;

use LDL\Http\Core\Request\Helper\RequestHelper;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Middleware\Config\MiddlewareConfigRepositoryInterface;
use LDL\Type\Collection\Types\String\StringCollection;
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
     * @var string
     */
    private $responseParser;

    /**
     * @var ?array
     */
    private $responseParserOptions;

    /**
     * @var ?string
     */
    private $responseFormatter;

    /**
     * @var ?array
     */
    private $responseFormatterOptions;

    /**
     * @var StringCollection
     */
    private $requestValidators;

    /**
     * @var ?string
     */
    private $requestParser;

    /**
     * @var StringCollection
     */
    private $responseValidators;

    /**
     * @var StringCollection
     */
    private $requestConfigurators;

    /**
     * @var MiddlewareConfigRepositoryInterface
     */
    private $preDispatchers;

    /**
     * @var MiddlewareConfigRepositoryInterface
     */
    private $dispatchers;

    /**
     * @var MiddlewareConfigRepositoryInterface
     */
    private $postDispatchers;

    /**
     * @var StringCollection
     */
    private $exceptionHandlerList;

    /**
     * @var array
     */
    private $rawConfig;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $responseSuccess;

    public function __construct(
        string $method,
        string $version,
        string $prefix,
        string $name,
        string $description,
        MiddlewareConfigRepositoryInterface $preDispatchers,
        MiddlewareConfigRepositoryInterface $dispatchers,
        MiddlewareConfigRepositoryInterface $postDispatchers,
        StringCollection $requestValidators,
        StringCollection $responseValidators,
        StringCollection $requestConfigurators,
        StringCollection $exceptionHandlerList,
        ?int $responseSuccess,
        ?string $requestParser,
        ?string $responseParser,
        ?array $responseParserOptions,
        ?string $responseFormatter,
        ?array $responseFormatterOptions,
        ?array $rawConfig,
        string $file = null
    )
    {

        $this->setPrefix($prefix)
            ->setName($name)
            ->setVersion($version)
            ->setRequestMethod($method)
            ->setPreDispatchers($preDispatchers)
            ->setDispatchers($dispatchers)
            ->setPostDispatchers($postDispatchers)
            ->setResponseParser($responseParser)
            ->setResponseParserOptions($responseParserOptions)
            ->setResponseFormatter($responseFormatter)
            ->setResponseFormatterOptions($responseFormatterOptions)
            ->setDescription($description)
            ->setRequestValidators($requestValidators)
            ->setResponseValidators($responseValidators)
            ->setRequestConfigurators($requestConfigurators)
            ->setResponseSuccess($responseSuccess ?? ResponseInterface::HTTP_CODE_OK)
            ->setExceptionHandlers($exceptionHandlerList)
            ->setRequestParser($requestParser)
            ->setRawConfig($rawConfig ?? [])
            ->setFile($file);
    }

    public static function fromArray(array $config) : self
    {
        $merge = array_merge(get_class_vars(__CLASS__), $config);
        return new self(...$merge);
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
     * @return string
     */
    public function getRequestMethod() : string
    {
        return $this->requestMethod;
    }

    /**
     * @return string|null
     */
    public function getResponseParser() : ?string
    {
        return $this->responseParser;
    }

    public function getResponseParserOptions() : ?array
    {
        return $this->responseParserOptions;
    }

    public function getResponseFormatter() : ?string
    {
        return $this->responseFormatter;
    }

    public function getResponseFormatterOptions() : ?array
    {
        return $this->responseFormatterOptions;
    }

    public function getFile() : string
    {
        return $this->file;
    }

    public function getRequestValidators() : StringCollection
    {
        return $this->requestValidators;
    }

    public function getResponseValidators() : StringCollection
    {
        return $this->responseValidators;
    }

    public function getRequestConfigurators() : StringCollection
    {
        return $this->requestConfigurators;
    }

    public function getPreDispatchers() : MiddlewareConfigRepositoryInterface
    {
        return $this->preDispatchers;
    }

    public function getDispatchers() : MiddlewareConfigRepositoryInterface
    {
        return $this->dispatchers;
    }

    public function getPostDispatchers() : MiddlewareConfigRepositoryInterface
    {
        return $this->postDispatchers;
    }

    public function getExceptionHandlerList() : StringCollection
    {
        return $this->exceptionHandlerList;
    }

    public function getRawConfig() : array
    {
        return $this->rawConfig;
    }

    public function getRequestParser() : ?string
    {
        return $this->requestParser;
    }


    public function getResponseSuccessCode() : int
    {
        return $this->responseSuccess;
    }

    //<editor-fold desc="Private methods">

    private function setRequestParser(?string $parser) : self
    {
        $this->requestParser = $parser;
        return $this;
    }

    private function setFile(string $file) : self
    {
        $this->file = $file;
        return $this;
    }

    private function setRawConfig(array $config) : self
    {
        $this->rawConfig = $config;
        return $this;
    }

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

    private function setResponseParser(string $parser=null) : self
    {
        $this->responseParser = $parser;
        return $this;
    }

    private function setResponseParserOptions(?array $options) : self
    {
        $this->responseParserOptions = $options;
        return $this;
    }

    private function setResponseFormatter(?string $name) : self
    {
        $this->responseFormatter = $name;
        return $this;
    }

    private function setResponseFormatterOptions(?array $options) : self
    {
        $this->responseFormatterOptions = $options;
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

    private function setRequestValidators(StringCollection $validators) : self
    {
        $this->requestValidators = $validators;
        return $this;
    }

    private function setResponseValidators(StringCollection $validators) : self
    {
        $this->responseValidators = $validators;
        return $this;
    }

    private function setRequestConfigurators(StringCollection $configurators) : self
    {
        $this->requestConfigurators = $configurators;
        return $this;
    }

    private function setPreDispatchers(MiddlewareConfigRepositoryInterface $preDispatchers) : self
    {
        $this->preDispatchers = $preDispatchers;
        return $this;
    }

    private function setDispatchers(MiddlewareConfigRepositoryInterface $dispatchers) : self
    {
        $this->dispatchers = $dispatchers;
        return $this;
    }

    private function setPostDispatchers(MiddlewareConfigRepositoryInterface $postDispatchers) : self
    {
        $this->postDispatchers = $postDispatchers;
        return $this;
    }

    private function setExceptionHandlers(StringCollection $handlers) : self
    {
        $this->exceptionHandlerList = $handlers;
        return $this;
    }

    private function setResponseSuccess(int $code) : self
    {
        $this->responseSuccess = $code;
        return $this;
    }

    //</editor-fold>
}