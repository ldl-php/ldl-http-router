<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Json;

use LDL\Http\Router\Response\Parser\ResponseParserInterface;
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
        ?string $name = null,
        array $options = []
    )
    {
        parent::__construct($name ?? self::NAME, $options);
    }

    public function setOptions(?array $options): ResponseParserInterface
    {
        parent::setOptions($options);

        if(null !== $options && array_key_exists('content-type', $options)) {
            $this->contentType = $options['content-type'];
            return $this;
        }

        $this->contentType = self::RESPONSE_CONTENT_TYPE;
        return $this;
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

        $defaults = [
            'options' => '\JSON_THROW_ON_ERROR',
            'depth' => 2048
        ];

        $options = array_replace_recursive($defaults, $this->getOptions() ?? []);
        $jsonOptions = explode(' ', $options['options']);

        $optResult = null;

        $operator = null;
        foreach($jsonOptions as $key => $opt){
            $opt = trim($opt);

            if('' === $opt){
                continue;
            }

            $opt = strtoupper($opt);

            if(0 === $key){

                if(!defined($opt)){
                    throw new \LogicException("Undefined constant name: \"$opt\"");
                }

                $optResult = constant($opt);
                continue;
            }

            switch($opt){
                case '|':
                    $operator = 'OR';
                    continue 2;
                break;

                case '&':
                    $operator = 'AND';
                    continue 2;
                break;
            }

            if('OR' === $operator){
                $optResult |= constant($opt);
                continue;
            }

            if('AND' === $operator){
                $optResult &= constant($opt);
                continue;
            }

            $msg = sprintf('Invalid operator: "%s", "and" | "or" expected', $operator);
            throw new \LogicException($msg);
        }

        return json_encode(
            $data,
            $optResult,
            $options['depth']
        );
    }
}