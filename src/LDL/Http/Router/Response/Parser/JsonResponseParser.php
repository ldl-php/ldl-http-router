<?php

namespace LDL\Http\Router\Response\Parser;

class JsonResponseParser implements ResponseParserInterface
{
    public const RESPONSE_CONTENT_TYPE = 'application/json';

    /**
     * @var string
     */
    private $contentType;

    public function __construct(
        string $contentType=null
    )
    {
        $this->contentType = $contentType ??  self::RESPONSE_CONTENT_TYPE;
    }

    public function getContentType() : string
    {
        return $this->contentType;
    }

    public function parse(array $data): string
    {
        return json_encode($data);
    }
}