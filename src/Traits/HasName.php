<?php

namespace OpenDialogAi\Core\Traits;

use OpenDialogAi\Core\Exceptions\NameNotSetException;

trait HasName
{
    /**
     * @return string
     * @throws NameNotSetException
     */
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
