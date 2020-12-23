<?php declare(strict_types=1);

namespace LDL\Http\Router\Request\Body\Parser;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Traits\Selection\SingleSelectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Traits\Validator\ValueValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueValidator;

class RequestBodyParserRepository extends ObjectCollection implements RequestBodyParserRepositoryInterface
{
    use KeyValidatorChainTrait;
    use ValueValidatorChainTrait;
    use SingleSelectionTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValueValidatorChain()
            ->append(new InterfaceComplianceItemValidator(RequestBodyParserInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueValidator())
            ->lock();
    }

    /**
     * @param RequestBodyParserInterface $item
     * @param null $key
     * @return CollectionInterface
     * @throws \Exception
     */
    public function append($item, $key = null): CollectionInterface
    {
        return parent::append($item, $item->getName());
    }

    public function match(string $contentType) : RequestBodyParserInterface
    {
        /**
         * @var RequestBodyParserInterface $decoder
         */
        foreach($this as $decoder){
            if($decoder->getContentTypes()->hasValue($contentType)){
                return $decoder;
            }
        }

        $msg = "Could not find a parser matching content type: \"$contentType\"";
        throw new Exception\NoParserAvailableException($msg);
    }

}