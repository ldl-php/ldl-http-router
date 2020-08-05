<?php declare(strict_types=1);

namespace LDL\HTTP\Router\Route\Group;

class RouteGroup implements RouteGroupInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    public function __construct(
        string $prefix,
        RouteCollection $routes = null,
        string $name='',
        string $description=''
    )
    {
        $this->routes = $routes ?? new RouteCollection();
        $this->prefix = $prefix;
        $this->name = $name;
        $this->description = $description;
    }

    public function getName() : string
    {
       return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function  getRoutes(): RouteCollection
    {
        return $this->routes;
    }

}