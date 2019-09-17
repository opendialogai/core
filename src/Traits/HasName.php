<?php

declare(strict_types=1);

namespace OpenDialogAi\Core\Traits;

use OpenDialogAi\Core\Exceptions\NameNotSetException;

trait HasName
{
    public static function getName(): string
    {
        if (static::$name !== self::$name) {
            throw new NameNotSetException(
                sprintf(
                    "%s has not defined a name",
                    __CLASS__
                )
            );
        }

        return static::$name;
    }
}
