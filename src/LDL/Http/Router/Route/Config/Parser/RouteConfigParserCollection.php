<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use Psr\Container\ContainerInterface;

class RouteConfigParserCollection extends ObjectCollection
{
    /**
     * @var array
     */
    private $config;

    private $route;

    private $container;

    private $file;

    public function __construct(
        iterable $items = null,
        array $config=[]
    )
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteConfigParserInterface::class))
            ->lock();

        $this->config = $config;
    }

    public function init(
        array $config,
        ContainerInterface $container=null,
        string $file = null
    ): RouteConfigParserCollection
    {
        $this->config = $config;
        $this->container = $container;
        $this->file = $file;

        return $this;
    }

    public function parse(RouteInterface $route) : void
    {
        /**
         * @var RouteConfigParserInterface $configParser
         */
        foreach($this as $configParser){
            $configParser->parse(
                $this->config,
                $route,
                $this->container,
                $this->file
            );
        }
    }

}