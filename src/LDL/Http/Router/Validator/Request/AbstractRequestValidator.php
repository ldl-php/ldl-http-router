<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Request;

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

    final public function validate(Router $router): RequestValidatorInterface
    {
        try{

            $this->result = $this->_validate($router);
            return $this;

        }catch(Exception\RequestValidateException $e){
            if($this->isStrict){
                throw new Exception\RequestValidationTerminateException($e->getMessage(), $e->getCode());
            }

            throw new Exception\RequestValidationPartialException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Router $router
     * @return array|null
     * @throws Exception\RequestValidateException
     */
    abstract protected function _validate(Router $router) : ?array;
}