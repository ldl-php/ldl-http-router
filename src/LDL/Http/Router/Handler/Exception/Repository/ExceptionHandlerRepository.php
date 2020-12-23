<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Repository;

use LDL\Http\Core\Response\ResponseInterface;
use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Http\Router\Handler\Exception\ModifiesResponseInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Traits\Selection\MultipleSelectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueValidator;

class ExceptionHandlerRepository extends ObjectCollection implements ExceptionHandlerRepositoryInterface
{
    use MultipleSelectionTrait;
    use KeyValidatorChainTrait;

    /**
     * @var ExceptionHandlerInterface|null
     */
    private $lastExecutedExceptionHandler;

    /**
     * @var int
     */
    private $responseCode;

    /**
     * @var mixed
     */
    private $content;

    /**
     * @var bool
     */
    private $wasHandled;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(ExceptionHandlerInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
            ->lock();
    }

    /**
     * @param ExceptionHandlerInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function getLastExecutedExceptionHandler(): ?ExceptionHandlerInterface
    {
        return $this->lastExecutedExceptionHandler;
    }

    public function getResponseCode() : int
    {
        return $this->responseCode;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $e) : ExceptionHandlerRepositoryInterface
    {
        /**
         * Default to 500 if there is no handler returning an HTTP Response code
         */
        $this->responseCode = ResponseInterface::HTTP_CODE_INTERNAL_SERVER_ERROR;

        /**
         * Default to exception message
         */
        $this->content = $e->getMessage();

        $this->wasHandled = false;

        if(0 === $this->count()){
            return $this;
        }

        $exceptionClass = get_class($e);

        /**
         * @var ExceptionHandlerInterface $handler
         */
        foreach($this as $handler){

            if(false === $handler->canHandle($exceptionClass)){
                continue;
            }

            $this->wasHandled = true;

            $this->lastExecutedExceptionHandler = $handler;

            $httpStatusCode = $handler->handle($e);

            if(null !== $httpStatusCode && ($httpStatusCode >= 100 && $httpStatusCode <= 599)){
                $this->responseCode = $httpStatusCode;
            }

            if($handler instanceof ModifiesResponseInterface && null !== $handler->getContent()){
                $this->content = $handler->getContent();
            }
        }

        return $this;
    }

    public function wasHandled() : bool
    {
        if(null === $this->wasHandled){
            throw new \LogicException(
                sprintf(
                    '%s::handle(...) must we called first to get to know if the passed exception was handled',
                    __CLASS__
                )
            );
        }

        return $this->wasHandled;
    }

    public function canHandle(string $class, bool $strict=false) : bool
    {
        /**
         * @var ExceptionHandlerInterface $handler
         */
        foreach($this as $handler){
            if($handler->getHandledExceptions()->hasValue($class)){
                return true;
            }

            if(false === $strict && $handler instanceof $class){
                return true;
            }
        }

        return false;

    }
}