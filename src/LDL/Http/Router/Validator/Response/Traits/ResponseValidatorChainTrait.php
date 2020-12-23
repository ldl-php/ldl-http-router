<?php declare(strict_types=1);

/**
 * Trait which complies to HasResponseValidatorCollection
 * @see \LDL\Http\Router\Validator\Response\HasResponseValidatorChainInterface
 */

namespace LDL\Http\Router\Validator\Response\Traits;

use LDL\Http\Router\Validator\Response\ResponseValidatorChain;
use LDL\Http\Router\Validator\Response\ResponseValidatorChainInterface;

trait ResponseValidatorChainTrait
{
    private $_tResponseValidatorChain;

    public function getResponseValidatorChain() : ResponseValidatorChainInterface
    {
        if(null === $this->_tResponseValidatorChain){
            $this->_tResponseValidatorChain = new ResponseValidatorChain();
        }

        return $this->_tResponseValidatorChain;
    }

}