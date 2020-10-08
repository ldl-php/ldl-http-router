<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser;

use LDL\Framework\Base\Contracts\NamespaceInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Types\Object\Interfaces\KeyResolverInterface;

interface ResponseParserInterface extends NamespaceInterface, KeyResolverInterface
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