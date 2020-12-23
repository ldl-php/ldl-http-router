<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Mapper;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface RequestParameterMapperCollectionInterface extends CollectionInterface, HasValueValidatorChainInterface
{

}