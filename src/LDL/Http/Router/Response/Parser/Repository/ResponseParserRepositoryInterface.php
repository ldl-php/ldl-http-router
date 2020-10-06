<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Repository;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Namespaceable\NamespaceableInterface;
use LDL\Type\Collection\Interfaces\Selection\SingleSelectionInterface;

interface ResponseParserRepositoryInterface extends CollectionInterface, NamespaceableInterface, SingleSelectionInterface
{

}