<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Request;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Framework\Base\Traits\PriorityInterfaceTrait;
use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Exception\ExceptionCollection;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Nameable\NameableCollectionTrait;
use LDL\Type\Collection\Traits\Selection\MultipleSelectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\ClassComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueValidator;

class RequestValidatorChain extends ObjectCollection implements RequestValidatorChainInterface
{
    use MultipleSelectionTrait;
    use ValueValidatorChainTrait;
    use KeyValidatorChainTrait;
    use NameableTrait;
    use PriorityInterfaceTrait;
    use NameableCollectionTrait;

    private const NAME = 'request.validator.chain';
    private const PRIORITY = 1;

    /**
     * @var AbstractRequestValidator|null
     */
    private $lastExecutedValidator;

    /**
     * @var ExceptionCollection
     */
    private $exceptions;

    /**
     * @var bool
     */
    private $validated = false;

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
     * @return RequestValidatorChainInterface
     * @throws \Exception
     */
    public function getNewInstance(bool $strict = true) : RequestValidatorChainInterface
    {
        $self = clone($this);
        $self->truncate();

        foreach($this as $validator){
            $self->append($validator->getNewInstance($strict));
        }

        return $self;
    }

    public function getExceptions(): ExceptionCollection
    {
        return $this->exceptions;
    }

    public function validate(Router $router) : RequestValidatorInterface
    {
        $this->validated = true;
        $this->partialExceptions = new ExceptionCollection();

        if(0 === count($this)){
            return $this;
        }

        /**
         * @var \Exception[]
         */
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

                if(false === $validator->isStrict()){
                    $this->partialExceptions[] = $e;
                    continue;
                }
                throw new Exception\RequestValidationTerminateException($e->getMessage());

                $atLeastOneValid = false;
                break;
            }
        }

        if($atLeastOneValid){
            return $this;
        }

        throw new Exception\RequestValidationTerminateException(
            json_encode($this->partialExceptions, \JSON_THROW_ON_ERROR),
            ResponseInterface::HTTP_CODE_BAD_REQUEST
        );
    }

    public function getLastExecutedValidator(): ?AbstractRequestValidator
    {
        return $this->lastExecutedValidator;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }
}