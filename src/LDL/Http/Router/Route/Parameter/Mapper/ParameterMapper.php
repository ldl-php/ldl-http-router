<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter\Mapper;

use LDL\Http\Router\Route\Config\MiddlewareConfigInterface;

class ParameterMapper implements MiddlewareParameterMapperInterface
{
    private const FROM_REQUEST = 'request';
    private const FROM_RESPONSE = 'response';

    /**
     * Parameters need to come from the dispatcher configuration, not from comments, if you add it in the comments
     * then the reuse of the dispatcher will be not reusable.
     */

    /**
     * {@inheritdoc}
     */
    public function map(MiddlewareConfigInterface $config): ?array
    {
        return [];

    }

}