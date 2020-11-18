<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Json;

use LDL\Http\Router\Router;
use LDL\Http\Router\Response\Parser\AbstractResponseParser;

class JsonResponseParser extends AbstractResponseParser
{
    public const NAME = 'ldl.response.parser.json';
    public const RESPONSE_CONTENT_TYPE = 'application/json';

    /**
     * @var string
     */
    private $contentType;

    public function __construct(
        string $contentType=null
    )
    {
        parent::__construct(self::NAME);
        $this->contentType = $contentType ??  self::RESPONSE_CONTENT_TYPE;
    }

    public function getContentType() : string
    {
        return $this->contentType;
    }

    public function _parse(
        Router $router,
        ?array $data
    ) : ?string
    {
        if(null === $data){
            return null;
        }

        return json_encode($data, \JSON_THROW_ON_ERROR);
    }
}