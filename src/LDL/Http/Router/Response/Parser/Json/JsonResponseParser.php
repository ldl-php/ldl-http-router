<?php

namespace LDL\Http\Router\Response\Parser\Json;

use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Http\Router\Router;

class JsonResponseParser implements ResponseParserInterface
{
    public const NAMESPACE = 'ldl.response.parser';
    public const NAME = 'json';
    public const RESPONSE_CONTENT_TYPE = 'application/json';

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function getName(): string
    {
        return self::NAME;
    }

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

    public function parse(array $data, string $context, Router $router): string
    {
        return json_encode($data, \JSON_THROW_ON_ERROR);
    }
}