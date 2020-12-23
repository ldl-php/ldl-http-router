<?php declare(strict_types=1);

namespace LDL\Http\Router\Container\Source;

class ContainerSourceCallback extends ContainerSource implements Contract\RouteParameterSourceCallbackInterface
{
    /**
     * @var \Closure
     */
    private $callback;

    public function __construct(
        string $sourceName,
        string $method,
        $object,
        callable $callback
    )
    {
        parent::__construct($sourceName, $method, $object);
        $this->callback = $callback;
    }

    public function getCallback() : callable
    {
        return $this->callback;
    }

}