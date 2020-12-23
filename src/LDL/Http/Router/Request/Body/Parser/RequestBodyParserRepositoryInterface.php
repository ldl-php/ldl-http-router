<?php declare(strict_types=1);

namespace LDL\Http\Router\Request\Body\Parser;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Selection\SingleSelectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;
use LDL\Type\Collection\Interfaces\Validation\HasValueValidatorChainInterface;

interface RequestBodyParserRepositoryInterface extends CollectionInterface, HasKeyValidatorChainInterface, HasValueValidatorChainInterface, SingleSelectionInterface
{

    /**
     * @param string $contentType
     * @throws Exception\NoParserAvailableException
     * @return RequestBodyParserInterface
     */
    public function match(string $contentType) : RequestBodyParserInterface;

}