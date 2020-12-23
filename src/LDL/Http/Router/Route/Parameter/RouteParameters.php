<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

use LDL\Http\Router\Route\Parameter\Factory\RouteParameterFactory;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepository;
use LDL\Http\Router\Route\Parameter\Resolver\RouteParameterResolverRepositoryInterface;
use LDL\Type\Collection\Exception\UndefinedOffsetException;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Types\String\StringCollection;
use LDL\Type\Collection\Validator\UniqueValidator;

class RouteParameters extends ObjectCollection implements RouteParametersInterface
{
    use KeyValidatorChainTrait;

    /**
     * @var RouteParameterResolverRepository
     */
    private $resolvers;

    /**
     * @var array
     */
    private $values = [];

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteParameterInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
            ->lock();
    }

    public function getResolvers() : RouteParameterResolverRepositoryInterface
    {
        return $this->resolvers;
    }

    public static function create(
        array $routeParameters,
        string $defaultSource=null
    ) : RouteParametersInterface
    {
        $self = new self;
        $self->appendMany(
            array_map(static function($param) use ($defaultSource) {
                return RouteParameterFactory::fromArray(
                    $param,
                    $defaultSource
                );
            }, $routeParameters)
        );

        return $self;
    }

    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function getParameter(string $name) : RouteParameterInterface
    {
        try {
            return $this->offsetGet($name);
        }catch(UndefinedOffsetException $e){
            throw new UndefinedMiddlewareParameterException("Undefined parameter $name");
        }
    }

    public function get(string $name, bool $cache=true)
    {
        /**
         * @var RouteParameterInterface $parameter
         */
        foreach($this as $parameter){
            if(false === $parameter->isNamed($name)){
                continue;
            }

            if(true === $cache && array_key_exists($name, $this->values)){
                return $this->values[$name];
            }

            if($parameter->getResolver()){
                $resolver = $this->resolvers->offsetGet($parameter->getResolver());
                return $this->values[$name] = $resolver->resolve($parameter, $this);
            }

            return $this->values[$name] = $parameter->getValue();
        }

        throw new UndefinedMiddlewareParameterException("Undefined parameter: \"$name\"");
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function has(string $name): bool
    {
        /**
         * @var RouteParameterInterface $parameter
         */
        foreach($this as $parameter){
            if(
                $parameter->getName() === $name ||
                $parameter->getAliases()->hasKey($name)
            ){
                return true;
            }
        }

        return false;
    }

    public function hasParameters(iterable $parameters): ?StringCollection
    {
        $collection = new StringCollection();

        foreach($parameters as $param) {
            if(false === $this->has($param)){
                $collection->append($param);
            }
        }

        return $collection->count() > 0 ? $collection : null;
    }
}
