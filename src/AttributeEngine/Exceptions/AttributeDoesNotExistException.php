<?php

namespace OpenDialogAi\AttributeEngine\Exceptions;

/**
 * Should be thrown when trying to get an attribute from an attribute bag, but one has not been set
 */
class AttributeDoesNotExistException extends \RuntimeException
{
}
