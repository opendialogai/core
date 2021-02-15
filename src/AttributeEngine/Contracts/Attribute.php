<?php

namespace OpenDialogAi\AttributeEngine\Contracts;

/**
 * An Attribute is a perceivable feature of the environment (username, time, etc)
 * and through them entities (users, bots) and the environment in which they are
 * situated can be described.
 */
interface Attribute extends \JsonSerializable
{
    /**
     * The id (or name) of an attribute is a string indicating what describable feature
     * of the environment this attribute deals with.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * The type of attribute.
     *
     * @return string
     */
    public static function getType(): string;

    /**
     * A string representation of the attribute (typically suitable for display)
     *
     * @return string|null
     */
    public function toString(): ?string;

    /**
     * @return mixed
     */
    public function getValue();
}
