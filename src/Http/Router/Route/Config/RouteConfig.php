<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config;

use LDL\Http\Core\Request\Helper\RequestHelper;

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
    private $description = '';

    /**
     * @var string
     */
    private $contentType;

    /**
     * RouteConfig constructor.
     *
     * @param string $prefix
     * @param string $method
     * @param string $contentType
     * @param string $name
     * @param string $description
     * @throws Exception\InvalidHttpMethodException
     */
    public function __construct(
        string $prefix,
        string $method,
        string $contentType,
        string $name='',
        string $description=''
    )
    {
        $this->setPrefix($prefix)
        ->setMethod($method)
        ->setName($name)
        ->setDescription($description);
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

    /**
     * @return string
     */
    public function getContentType() : string
    {
        return $this->contentType;
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
            implode(', ', RequestHelper::getAvailableHttpMethods()),
            $this->prefix
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

    public function setPrefix(string $prefix) : self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function setDescription(string $description) : self
    {
        $this->description = $description;
        return $this;
    }

}