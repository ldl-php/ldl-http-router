<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Validator;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Http\Router\Router;

abstract class AbstractRequestValidator implements RequestValidatorInterface
{
    use NameableTrait;

    /**
     * @var bool
     */
    private $isStrict;

    /**
     * @var array|null
     */
    private $result;

    public function __construct(string $name=null, bool $isStrict = true)
    {
        $this->_tName = $name;
        $this->isStrict = $isStrict;
    }

    /**
     * @return bool
     */
    public function isStrict(): bool
    {
        return $this->isStrict;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function getNewInstance(bool $isStrict = true) : AbstractRequestValidator
    {
        $obj = clone($this);
        $obj->isStrict = $isStrict;

        return $obj;
    }

    final public function validate(Router $router): void
    {
        try{
            $this->result = $this->_validate($router);
        }catch(Exception\ValidateException $e){
            if($this->isStrict){
                throw new Exception\ValidationTerminateException($e->getMessage(), $e->getCode());
            }

            throw new Exception\ValidationPartialException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Router $router
     * @return array|null
     * @throws Exception\ValidateException
     */
    abstract protected function _validate(
        Router $router
    ) : ?array;
}