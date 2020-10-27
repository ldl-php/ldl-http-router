<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser;

use LDL\Http\Router\Router;
use LDL\Type\Collection\Types\Object\Interfaces\KeyResolverInterface;

interface ResponseParserInterface extends KeyResolverInterface
{
    /**
     * @return string
     */
    public function getContentType() : string;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param array $data data to parsed by the corresponding parser
     * @param Router $router Router object
     *
     * @return string
     */
    public function parse(
        array $data,
        Router $router
    ) : string;
}