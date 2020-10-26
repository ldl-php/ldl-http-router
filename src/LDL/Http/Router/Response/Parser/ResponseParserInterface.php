<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser;

use LDL\Framework\Base\Contracts\NamespaceInterface;
use LDL\Http\Router\Router;
use LDL\Type\Collection\Types\Object\Interfaces\KeyResolverInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ResponseParserInterface extends NamespaceInterface, KeyResolverInterface
{
    /**
     * @return string
     */
    public function getContentType() : string;

    /**
     * @param array $data data to parsed by the corresponding parser
     * @param Router $router Router object
     * @param ParameterBag|null $urlParameters
     *
     * @return string
     */
    public function parse(
        array $data,
        Router $router
    ) : string;
}