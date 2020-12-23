<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Source;

class ContainerSource implements Contract\RouteParameterSourceInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $object;

    public function __construct(
        string $sourceName,
        string $method,
        $object
    )
    {
        $this->name = $sourceName;
        $this->method = $method;
        $this->object = $object;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getObject()
    {
        return $this->object;
    }

}