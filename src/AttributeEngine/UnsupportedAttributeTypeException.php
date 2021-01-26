<?php

namespace OpenDialogAi\AttributeEngine;

/**
 * Thrown if we try to create an attribute of a type that is not supported.
 *
 * This is a LogicException so should lead to the code being fixed to avoid
 * its re-occurence.
 */
class UnsupportedAttributeTypeException extends \InvalidArgumentException
{
}
