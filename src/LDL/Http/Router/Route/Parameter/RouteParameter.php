<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

use LDL\Type\Collection\Types\String\StringCollection;

class RouteParameter implements RouteParameterInterface
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $name;

    /**
     * @var StringCollection
     */
    private $aliases;

    /**
     * @var ?string
     */
    private $resolver;

    public function __construct(
        string $source,
        string $name,
        string $resolver=null,
        StringCollection $aliases = null
    )
    {
        $this->source = $source;
        $this->name = $name;
        $this->resolver = $resolver;
        $this->aliases = $aliases ?? new StringCollection();
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNamed(string $name): bool
    {
        if($name === $this->name){
            return true;
        }

        foreach($this->aliases as $alias){
            if($name === $alias){
                return true;
            }
        }

        return false;
    }

    public function getAliases() : StringCollection
    {
        return $this->aliases;
    }

    public function getResolver() : ?string
    {
        return $this->resolver;
    }

}
