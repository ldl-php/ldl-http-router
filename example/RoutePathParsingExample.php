<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use LDL\Router\Core\Route\Path\RoutePathParser;
$parse = new RoutePathParser();

$route = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '/frontend/:name';
$parsed = $parse->parse($route);

echo "\nRoute information: $route\n";
echo "#######################################################\n\n";
echo "Parsed path: {$parsed->getPath()}\n";
echo sprintf('Is dynamic?: %s%s', $parsed->isDynamic() ? 'YES' : 'NO', "\n");
echo "Available placeholders:\n";
dump($parsed->getPlaceHolders());
