<?php declare(strict_types=1);

namespace LDL\Http\Router\Config;

use LDL\Type\Collection\Types\String\StringCollection;

class RouterConfig implements RouterConfigInterface
{
    /**
     * @var StringCollection
     */
    private $preDispatchList;

    /**
     * @var StringCollection
     */
    private $postDispatchList;

    public function __construct(
        StringCollection $preDispatchList,
        StringCollection $postDispatchList
    )
    {
        $this->preDispatchList = $preDispatchList;
        $this->postDispatchList = $postDispatchList;
    }

    public function getPreDispatchList() : StringCollection
    {
        return $this->preDispatchList;
    }

    public function getPostDispatchList() : StringCollection
    {
        return $this->postDispatchList;
    }

}