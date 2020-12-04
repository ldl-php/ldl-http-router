<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Selection\SingleSelectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface ResponseFormatterRepositoryInterface extends CollectionInterface, HasValueValidatorChainInterface, HasKeyValidatorChainInterface, SingleSelectionInterface
{

}