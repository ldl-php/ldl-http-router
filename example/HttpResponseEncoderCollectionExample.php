<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Router\Http\Response\Encoder\Collection\HttpResponseEncoderCollection;
use LDL\Router\Http\Response\Encoder\JsonHttpResponseEncoder;

$encoder = new JsonHttpResponseEncoder();
$collection = new HttpResponseEncoderCollection([
   $encoder,
]);

$found = $collection->findByName($encoder->getName());

if (null === $found) {
    throw new \Exception("{$encoder->getName()} NOT FOUND!");
}

echo "FOUND!\n";
