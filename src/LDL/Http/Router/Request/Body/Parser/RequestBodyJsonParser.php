<?php declare(strict_types=1);

namespace LDL\Http\Router\Request\Body\Parser;

use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Type\Collection\Types\String\StringCollection;

class RequestBodyJsonParser implements RequestBodyParserInterface
{
    use NameableTrait;

    /**
     * Default decoder name
     */
    private const NAME = 'ldl.request.json.parser';

    /**
     * Default JSON decode depth
     */
    private const DEPTH = 2048;

    /**
     * Default JSON decode flags
     */
    private const FLAGS = \JSON_THROW_ON_ERROR;

    /**
     * @var array
     */
    private $options;

    /**
     * @var StringCollection
     */
    private $contentTypes;

    /**
     * @var bool
     */
    private $assoc;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var array
     */
    private $parsed;

    public function __construct(
        string $name=null,
        array $options=[],
        StringCollection $contentTypes = null
    )
    {
        $this->_tName = $name ?? self::NAME;
        $this->contentTypes = $contentTypes ?? new StringCollection(['application/json']);
        $this->flags = $this->getFlags($options);
        $this->assoc = $this->getAssoc($options);
        $this->depth = $this->getDepth($options);
    }

    public function parse(string $body, bool $cache = true) : array
    {
        if(null !== $this->parsed){
            return $this->parsed;
        }

        $valid = false;
        $error = '';
        $content = [];

        try {

            $content = json_decode(
                $body,
                $this->assoc,
                $this->depth,
                $this->flags
            );

            $valid = true;

            /**
             * In case JSON_THROW_ON_ERROR wasn't given as part of the options
             */
            if(json_last_error() !== JSON_ERROR_NONE){
                $valid = false;
                $error = json_last_error_msg();
            }
        }catch(\JsonException $e){
            $valid = false;
            $error = $e->getMessage();
        }

        if(false === $valid){
            throw new Exception\RequestBodyParseException($error);
        }

        return $this->parsed = $content;
    }

    public function getContentTypes(): StringCollection
    {
        return $this->contentTypes;
    }

    //<editor-fold desc="private methods">
    private function getDepth(array $options) : int
    {
        if(false === array_key_exists('depth', $options)){
            return self::DEPTH;
        }

        return !is_int($options['depth']) ? self::DEPTH : $options['depth'];
    }

    private function getAssoc(array $options) : bool
    {
        if(false === array_key_exists('assoc', $options)){
            return true;
        }

        return !is_bool($options['depth']) ? true : $options['depth'];
    }

    private function getFlags(array $options) : int
    {
        $hasFlags = array_key_exists('flags', $options);

        if(false === $hasFlags){
            return self::FLAGS;
        }

        $flags = $options['flags'];

        if(is_int($flags)){
            return $flags;
        }

        if(is_string($flags)){
            return constant($flags);
        }

        $msg = sprintf(
            'JSON decode flags must be of type int or string, "%s" was given',
            gettype($flags)
        );

        throw new \InvalidArgumentException($msg);
    }

    //</editor-fold>
}