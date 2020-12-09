<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Validator;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Framework\Base\Traits\PriorityInterfaceTrait;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Nameable\NameableCollectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\ClassComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueValidator;

class RequestValidatorChain extends ObjectCollection implements RequestValidatorChainInterface
{
    private const NAME = 'request.validator.chain';
    private const PRIORITY = 1;

    use ValueValidatorChainTrait;
    use KeyValidatorChainTrait;
    use NameableTrait;
    use PriorityInterfaceTrait;
    use NameableCollectionTrait;

    /**
     * @var AbstractRequestValidator|null
     */
    private $lastExecutedValidator;

    /**
     * @var array
     */
    private $partialExceptions = [];

    public function __construct(string $name = null, int $priority = null, iterable $items = null)
    {
        parent::__construct($items);

        $this->_tName = $name ?? self::NAME;
        $this->_tPriority = $priority ?? self::PRIORITY;

        $this->getValueValidatorChain()
            ->append(new ClassComplianceItemValidator(AbstractRequestValidator::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
            ->lock();
    }

    /**
     * @param AbstractRequestValidator $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, mb_strtolower($item->getName()));
    }

    /**
     * @param bool $strict
     * @return AbstractRequestValidator
     * @throws \Exception
     */
    public function getNewInstance(bool $strict = true) : AbstractRequestValidator
    {
        $self = clone($this);
        $self->truncate();

        foreach($this as $validator){
            $self->append($validator->getNewInstance($strict));
        }

        return $self;
    }

    /**
     * @return array
     */
    public function getPartialExceptions(): array
    {
        return $this->partialExceptions;
    }

    public function validate(Router $router) : void
    {
        if(0 === count($this)){
            return;
        }

        /**
         * @var \Exception[]
         */
        $this->partialExceptions = [];
        $atLeastOneValid = false;

        /**
         * @var AbstractRequestValidator $validator
         */
        foreach($this as $validator){
            $this->lastExecutedValidator = $validator;

            try{
                $validator->validate($router);
                $atLeastOneValid = true;
            }catch(\Exception $e){
                $this->partialExceptions[$validator->getName()] = $e->getMessage();

                if(false === $validator->isStrict()){
                    continue;
                }

                if($e instanceof Exception\ValidationTerminateException){
                    $atLeastOneValid = false;
                    break;
                }
            }
        }

        if($atLeastOneValid){
            return;
        }

        throw new Exception\ValidationTerminateException(
            json_encode($this->partialExceptions, \JSON_THROW_ON_ERROR),
            ResponseInterface::HTTP_CODE_BAD_REQUEST
        );
    }

    public function getLastExecutedValidator(): ?AbstractRequestValidator
    {
        return $this->lastExecutedValidator;
    }
}