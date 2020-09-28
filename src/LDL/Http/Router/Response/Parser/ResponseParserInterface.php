<?php

namespace LDL\Http\Router\Response\Parser;

use LDL\Framework\Contracts\NamespaceInterface;
use LDL\Http\Router\Router;

interface ResponseParserInterface extends NamespaceInterface
{
    /**
     * @return string
     */
    public function getContentType() : string;

    /**
     * @param array $data data to parsed by the corresponding parser
     * @param string $context Indicates the context in which data has to be parsed
     * @param Router $router Router object
     *
     * @return string
     */
    public function parse(
        array $data,
        string $context,
        Router $router
    ) : string;
}