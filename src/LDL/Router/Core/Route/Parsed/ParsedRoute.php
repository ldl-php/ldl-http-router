<?php

declare(strict_types=1);

namespace LDL\Router\Core\Route\Parsed;

use LDL\Framework\Base\Traits\DescribableInterfaceTrait;
use LDL\Framework\Base\Traits\NameableTrait;
use LDL\Framework\Helper\IterableHelper;
use LDL\Router\Core\Route\Dispatcher\Collection\RouteDispatcherCollectionInterface;
use LDL\Router\Core\Route\Dispatcher\RouteDispatcherInterface;
use LDL\Validators\Chain\Dumper\ValidatorChainHumanDumper;
use LDL\Validators\Chain\ValidatorChainInterface;

class ParsedRoute implements ParsedRouteInterface
{
    use NameableTrait;
    use DescribableInterfaceTrait;

    /**
     * @var string
     */
    private $parsedPath;

    /**
     * @var string
     */
    private $originalPath;

    /**
     * @var bool
     */
    private $isDynamic;

    /**
     * @var array
     */
    private $placeholders;

    /**
     * @var RouteDispatcherCollectionInterface
     */
    private $dispatchers;

    /**
     * @var ValidatorChainInterface
     */
    private $validatorChain;

    public function __construct(
        string $name,
        string $description,
        string $parsedPath,
        string $originalPath,
        bool $isDynamic,
        array $placeHolders,
        RouteDispatcherCollectionInterface $dispatchers,
        ValidatorChainInterface $validatorChain
    ) {
        $this->_tName = $name;
        $this->_tDescription = $description;
        $this->parsedPath = $parsedPath;
        $this->originalPath = $originalPath;
        $this->isDynamic = $isDynamic;
        $this->placeholders = $placeHolders;
        $this->dispatchers = $dispatchers;
        $this->validatorChain = $validatorChain;
    }

    public function getParsedPath(): string
    {
        return $this->parsedPath;
    }

    public function getOriginalPath(): string
    {
        return $this->originalPath;
    }

    public function isDynamic(): bool
    {
        return $this->isDynamic;
    }

    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    public function getDispatchers(): RouteDispatcherCollectionInterface
    {
        return $this->dispatchers;
    }

    public function getValidatorChain(): ValidatorChainInterface
    {
        return $this->validatorChain;
    }

    public function toArray(bool $useKeys = null): array
    {
        return [
            'name' => $this->_tName,
            'description' => $this->_tDescription,
            'path' => [
                'parsed' => $this->parsedPath,
                'original' => $this->originalPath,
            ],
            'dynamic' => $this->isDynamic,
            'placeholders' => $this->placeholders,
            'dispatchers' => $this->dispatchers,
            'validators' => $this->validatorChain,
        ];
    }

    public function toPrimitiveArray(bool $preserveKeys = true): array
    {
        $array = $this->toArray();

        $array['dispatchers'] = IterableHelper::map($array['dispatchers'], static function (RouteDispatcherInterface $d): string {
            return get_class($d);
        });
        $array['validators'] = ValidatorChainHumanDumper::dump($this->validatorChain);

        return $preserveKeys ? $array : array_values($array);
    }
}
