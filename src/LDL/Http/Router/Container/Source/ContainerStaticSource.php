<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Source;

class ContainerStaticSource implements Contract\RouteParameterStaticSourceInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(
        string $sourceName,
        $value
    )
    {
        $this->name = $sourceName;
        $this->value = $value;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

}