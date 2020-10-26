<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }
}
