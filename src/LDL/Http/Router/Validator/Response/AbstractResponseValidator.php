<?php declare(strict_types=1);

namespace LDL\Http\Router\Validator\Response;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Http\Router\Router;

abstract class AbstractResponseValidator implements ResponseValidatorInterface
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

    public function getNewInstance(bool $isStrict = true) : AbstractResponseValidator
    {
        $obj = clone($this);
        $obj->isStrict = $isStrict;

        return $obj;
    }

    final public function validate(Router $router, array $responseResult = null): void
    {
        try{
            $this->result = $this->_validate($router, $responseResult);
        }catch(Exception\ResponseValidateException $e){
            if($this->isStrict){
                throw new Exception\ResponseValidationTerminateException($e->getMessage(), $e->getCode());
            }

            throw new Exception\ResponseValidationPartialException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Router $router
     * @param array|null $responseResult
     * @return array|null
     * @throws Exception\ResponseValidateException
     */
    abstract protected function _validate(
        Router $router,
        array $responseResult = null
    ) : ?array;
}