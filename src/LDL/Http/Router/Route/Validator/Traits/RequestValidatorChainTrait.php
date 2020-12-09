<?php declare(strict_types=1);

/**
 * Trait which complies to HasValidatorCollection
 * @see \LDL\Http\Router\Route\Validator\HasValidatorChainInterface
 */

namespace LDL\Http\Router\Route\Validator\Traits;

use LDL\Http\Router\Route\Validator\RequestValidatorChain;

trait RequestValidatorChainTrait
{
    private $_tValidatorChain;

    public function getValidatorChain() : RequestValidatorChain
    {
        if(null === $this->_tValidatorChain){
            $this->_tValidatorChain = new RequestValidatorChain();
        }

        return $this->_tValidatorChain;
    }

}