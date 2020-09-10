<?php declare(strict_types=1);

namespace LDL\Http\Router\Repository;

use LDL\Type\Collection\AbstractCollection;
use LDL\Type\Exception\TypeMismatchException;

class JsonRepository extends AbstractCollection implements JsonRepositoryInterface
{
    /**
     * @param $file
     * @throws Exception\JsonNotFoundException
     * @throws Exception\JsonUnreadableException
     * @throws TypeMismatchException
     */
    public function validateItem($file): void
    {
        if(!is_string($file)){
            $msg = sprintf(
                'Item must be a string, "%s" was given',
                gettype($file)
            );
            throw new TypeMismatchException($msg);
        }

        if(!file_exists($file)){
            $msg = "Json file \"$file\" not found!";
            throw new Exception\JsonNotFoundException($msg);
        }

        if(!is_readable($file)){
            $msg = "Could not read json file: \"$file\", permission denied";
            throw new Exception\JsonUnreadableException($msg);
        }
    }
}