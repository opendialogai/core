<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\Core\Components\OpenDialogComponentInterface;

/**
 * An Attribute is a perceivable feature of the environment (username, time, etc)
 * and through them entities (users, bots) and the environment in which they are
 * situated can be described.
 */
interface AttributeInterface extends OpenDialogComponentInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public static function getType(): string;

    /**
     * @param array $arg
     *
     * @return mixed
     */
    public function getValue(array $arg = []);

    /**
     * @param $value
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function toString(): string;

    /**
     * Returns a serialized version of the attribute
     *
     * @return string
     */
    public function serialized(): ?string;
}
