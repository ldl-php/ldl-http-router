<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Json;

use LDL\Http\Router\Router;
use LDL\Http\Router\Response\Parser\AbstractResponseParser;

class JsonResponseParser extends AbstractResponseParser
{
    public const NAME = 'ldl.json.response.parser';
    public const RESPONSE_CONTENT_TYPE = 'application/json';

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

    public function parse(
        array $data,
        Router $router
    ): string
    {
        return json_encode($data, \JSON_THROW_ON_ERROR);
    }
}