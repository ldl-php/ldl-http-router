<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/lib/example-http-dispatchers.php';

use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollection;

echo "Create a RouteDispatcherCollection and add 3 dispatchers ...\n";

$dispatchers = new RouteDispatcherCollection([
   new HttpDispatcherExample('dispatcher 1', ''),
    new HttpDispatcherExample('dispatcher 2', ''),
    new HttpDispatcherExample('dispatcher 3', ''),
]);

echo "Filter the collection by names: 'dispatcher 1' and 'dispatcher 2'\n";

$filtered = $dispatchers->filterByNames(['dispatcher 1', 'dispatcher 2']);

echo "Count of filtered collection must be equal to 2:\n";

if (2 !== count($filtered)) {
    throw new \RuntimeException('Count of filtered items is != 2!');
}

echo "OK count is == 2\n";

echo "Filter by UNKNOWN dispatcher names (test, test1), count must be 0\n";

if (0 !== count($dispatchers->filterByNames(['test', 'test1']))) {
    throw new \RuntimeException('Count is != to 0!');
}

echo "OK count is == 0\n";

echo "Get dispatcher by name: 'dispatcher 3'\n";

$dispatcher = $dispatchers->getByName('dispatcher 3');

if (null === $dispatcher) {
    throw new \RuntimeException('Dispatcher 3 could not be found!');
}

echo "OK dispatcher 3 was found\n";

echo "Get dispatcher UNKNOWN name: 'dispatcher 4'\n";

$dispatcher = $dispatchers->getByName('dispatcher 4');

if (null !== $dispatcher) {
    throw new \RuntimeException('Dispatcher is not null!');
}
