<?php declare(strict_types=1);

namespace LDL\Http\Router\Request\Body\Parser;

use LDL\Framework\Base\Contracts\NameableInterface;
use LDL\Type\Collection\Types\String\StringCollection;

interface RequestBodyParserInterface extends NameableInterface
{
    /**
     * Parse request content as an array
     *
     * @param string $body
     * @param bool $cache If it was already parsed, don't parse it again
     *
     * @throws Exception\RequestBodyParseException
     * @return array
     */
    public function parse(string $body, bool $cache=true) : array;

    /**
     * Returns a string collection of supported content types that this parser supports
     *
     * @return StringCollection
     */
    public function getContentTypes() : StringCollection;
}