<?php declare(strict_types=1);

/**
 * Trait which complies to HasValidatorCollection
 * @see \LDL\Http\Router\Validator\Request\HasValidatorChainInterface
 */

namespace LDL\Http\Router\Validator\Request\Traits;

use LDL\Http\Router\Validator\Request\RequestValidatorChain;
use LDL\Http\Router\Validator\Request\RequestValidatorChainInterface;

trait RequestValidatorChainTrait
{
    private $_tRequestValidatorChain;

    public function getRequestValidatorChain() : RequestValidatorChainInterface
    {
        if(null === $this->_tRequestValidatorChain){
            $this->_tRequestValidatorChain = new RequestValidatorChain();
        }

        return $this->_tRequestValidatorChain;
    }

}