<?php

declare(strict_types=1);

namespace LDL\Router\Http\Response\Encoder\Collection;

use LDL\Router\Http\Response\Encoder\HttpResponseEncoderInterface;
use LDL\Type\Collection\TypedCollectionInterface;

interface HttpResponseEncoderCollectionInterface extends TypedCollectionInterface
{
    public function findByName(string $name): ?HttpResponseEncoderInterface;
}
