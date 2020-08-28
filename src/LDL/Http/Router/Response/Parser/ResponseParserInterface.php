<?php

namespace LDL\Http\Router\Response\Parser;

interface ResponseParserInterface
{
    /**
     * @return string
     */
    public function getContentType() : string;

    /**
     * @param array $data
     * @return string
     */
    public function parse(array $data) : string;
}