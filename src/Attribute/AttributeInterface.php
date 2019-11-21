<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * An Attribute is a perceivable feature of the environment (username, time, etc)
 * and through them entities (users, bots) and the environment in which they are
 * situated can be described.
 */
interface AttributeInterface
{

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getType(): string;

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
     * @return string
     */
    public function getSerialized(): string;
}
