<?php

namespace OpenDialogAi\ContextEngine\ContextManager;

/**
 * Should be thrown when trying to get an attribute from an attribute bag, but one has not been set
 */
class ContextDoesNotExistException extends \RuntimeException
{

}
