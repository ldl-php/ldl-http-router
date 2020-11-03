<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Config\Parser;

use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;

class RouteConfigParserRepository extends ObjectCollection implements RouteConfigParserRepositoryInterface
{
    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RouteConfigParserInterface::class))
            ->lock();
    }

    public function parse(RouteInterface $route) : void
    {
        /**
         * @var RouteConfigParserInterface $configParser
         */
        foreach($this as $configParser){
            $configParser->parse($route);
        }
    }

}