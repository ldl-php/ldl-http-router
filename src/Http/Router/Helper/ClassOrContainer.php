<?php

declare(strict_types=1);

namespace LDL\Http\Router\Helper;

use Psr\Container\ContainerInterface;

class ClassOrContainer
{
    public static function get(array $data, ContainerInterface $container = null)
    {
        $hasClass = array_key_exists('class', $data);
        $hasContainer = array_key_exists('container', $data);

        if (!$hasClass && !$hasContainer) {
            $msg = 'Class or container section not found';
            throw new Exception\SectionNotFoundException($msg);
        }

        if ($hasContainer && $hasClass) {
            $msg = 'Must define class or container, can not define both';
            throw new Exception\SectionNotFoundException($msg);
        }

        if ($hasClass) {
            $className = $data['class'];

            if (!class_exists($className)) {
                $msg = "Class \"$className\" not found";
                throw new Exception\ClassNotFoundException($msg);
            }

            $arguments = array_key_exists('arguments', $data) ? $data['arguments'] : [];

            return new $className(...array_values($arguments));
        }

        if (null === $container) {
            $msg = 'Container section specified but no container was passed to this factory';
            throw new Exception\UndefinedContainerException($msg);
        }

        return $container->get($data['container']);
    }
}
