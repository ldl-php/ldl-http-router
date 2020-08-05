<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Group;

use LDL\Http\Router\Guard\RouterGuardCollection;

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

    /**
     * @var ?RouteGuardCollection
     */
    private $guards;

    public function __construct(
        string $name,
        string $prefix,
        RouteCollection $routes,
        RouterGuardCollection $guards=null,
        string $description=''
    )
    {
        $this->routes = $routes ?? new RouteCollection();
        $this->prefix = $prefix;
        $this->name = $name;
        $this->description = $description;
        $this->guards = $guards;
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

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function getGuards(): ?RouterGuardCollection
    {
        return $this->guards;
    }

}