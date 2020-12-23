<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Response;

use LDL\Framework\Base\Traits\NameableTrait;
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

class ResponseValidatorChain extends ObjectCollection implements ResponseValidatorChainInterface
{
    use ValueValidatorChainTrait;
    use KeyValidatorChainTrait;
    use NameableTrait;
    use NameableCollectionTrait;
    use MultipleSelectionTrait;

    private const NAME = 'response.validator.chain';

    /**
     * @var bool
     */
    private $validated=false;

    /**
     * @var AbstractResponseValidator|null
     */
    private $lastExecutedValidator;

    /**
     * @var ExceptionCollection
     */
    private $partialExceptions;

    public function __construct(string $name = null, iterable $items = null)
    {
        parent::__construct($items);

        $this->_tName = $name ?? self::NAME;

        $this->getValueValidatorChain()
            ->append(new ClassComplianceItemValidator(AbstractResponseValidator::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
            ->lock();

        $this->partialExceptions = new ExceptionCollection();
    }

    /**
     * @param AbstractResponseValidator $item
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
     * @return ResponseValidatorChainInterface
     * @throws \Exception
     */
    public function getNewInstance(bool $strict = true) : ResponseValidatorChainInterface
    {
        $self = clone($this);
        $self->truncate();

        foreach($this as $validator){
            $self->append($validator->getNewInstance($strict));
        }

        return $self;
    }

    public function validate(Router $router, array $responseResult = null) : void
    {
        $this->validated = true;

        if(0 === count($this)){
            return;
        }

        $atLeastOneValid = false;

        /**
         * @var AbstractResponseValidator $validator
         */
        foreach($this as $validator){
            $this->lastExecutedValidator = $validator;

            try{
                $validator->validate($router, $responseResult);
                $atLeastOneValid = true;
            }catch(\Exception $e){
                $this->partialExceptions->append($e, $validator->getName());

                if(false === $validator->isStrict()){
                    continue;
                }

                if($e instanceof Exception\ResponseValidationTerminateException){
                    $atLeastOneValid = false;
                    break;
                }
            }
        }

        if($atLeastOneValid){
            return;
        }

        throw new Exception\ResponseValidationTerminateException(
            json_encode($this->partialExceptions, \JSON_THROW_ON_ERROR)
        );
    }

    public function isValidated() : bool
    {
        return $this->validated;
    }

    public function getLastExecutedValidator(): ?AbstractResponseValidator
    {
        return $this->lastExecutedValidator;
    }

    public function getPartialExceptions(): ExceptionCollection
    {
        $msg = sprintf(
            '%s: Can not obtain partial exception list from a non validated validator chain',
            $this->_tName
        );

        if(false === $this->validated){
            throw new Exception\UndispatchedResponseValidationException($msg);
        }

        return $this->partialExceptions;
    }

}