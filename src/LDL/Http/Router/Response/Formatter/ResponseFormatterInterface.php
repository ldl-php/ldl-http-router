<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

use LDL\Http\Router\Middleware\Chain\Result\MiddlewareChainResultInterface;

interface ResponseFormatterInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * Get the format result
     * @return array|null
     */
    public function getResult() : ?array;

    /**
     * @param array|null $options
     * @return ResponseFormatterInterface
     */
    public function setOptions(?array $options) : ResponseFormatterInterface;

    /**
     * @return array|null
     */
    public function getOptions() : ?array;

    /**
     * @return bool
     */
    public function isFormatted() : bool;

    /**
     * Parse all the results returned by the RouterDispatcher
     *
     * @param MiddlewareChainResultInterface $result
     * @param bool $setFormatted
     *
     * @return ResponseFormatterInterface
     */
    public function format(
        MiddlewareChainResultInterface $result,
        bool $setFormatted = false
    ) : ResponseFormatterInterface;
}