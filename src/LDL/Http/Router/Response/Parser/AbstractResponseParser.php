<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser;

abstract class AbstractResponseParser implements ResponseParserInterface
{
    public function getItemKey(): string
    {
        return strtolower(sprintf('%s.%s', $this->getNamespace(), $this->getName()));
    }
}