<?php declare(strict_types=1);

namespace LDL\Http\Router\Guard;

use LDL\Type\Collection\Types\Object\ObjectCollection;

class RouterGuardCollection extends ObjectCollection
{
    public function validateItem($item): void
    {
        parent::validateItem($item);

        $msg = sprintf(
            'Expected instance of type: "%s", instance of type: "%s" was given',
            RouterGuardInterface::class,
            get_class($item)
        );

        throw new Exception\InvalidGuardObject($msg);
    }

    public function filterByType(string $guardType) : RouterGuardCollection
    {
        return new static(
            array_filter(
                \iterator_to_array($this),
                /**
                 * @var RouterGuardInterface $guard
                 * @return bool
                 */
                static function($guard) use ($guardType){
                    return $guard->getType() === $guardType;
                }
            )
        );
    }
}
