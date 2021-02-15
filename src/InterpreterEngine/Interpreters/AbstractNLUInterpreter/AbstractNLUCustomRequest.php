<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

abstract class AbstractNLUCustomRequest
{
    /**
     * This method gets the returned contents of the request.
     *
     * @return mixed
     */
    abstract public function getContents();

    /**
     * This method returns a boolean representing whether or not the request was successful or not
     *
     * @return bool
     */
    abstract public function isSuccessful(): bool;
}
