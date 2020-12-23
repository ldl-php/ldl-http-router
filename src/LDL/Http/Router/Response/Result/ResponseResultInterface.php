<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Result;

use LDL\Framework\Base\Contracts\LockableObjectInterface;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface ResponseResultInterface extends CollectionInterface, HasValueValidatorChainInterface, LockableObjectInterface
{

}