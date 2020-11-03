<?php declare(strict_types=1);

namespace LDL\Http\Router\Middleware;

use LDL\Framework\Base\Exception\LockingException;
use LDL\Http\Core\Request\RequestInterface;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Route\RouteInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Filter\FilterByActiveStateTrait;
use LDL\Type\Collection\Traits\Filter\FilterByInterfaceTrait;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Sorting\PrioritySortingTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueKeyValidator;
use Symfony\Component\HttpFoundation\ParameterBag;

class DispatcherRepository extends ObjectCollection
{
    use KeyValidatorChainTrait;
    use NamespaceableTrait;
    use ValueValidatorChainTrait;
    use PrioritySortingTrait;
    use FilterByInterfaceTrait;
    use FilterByActiveStateTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(MiddlewareInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueKeyValidator())
            ->lock();
    }

    /**
     * @param MiddlewareInterface $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, mb_strtolower($item->getName()));
    }
}
