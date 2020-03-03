<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

abstract class AbstractNLUClient implements AbstractNLUClientInterface
{
    /**
     * @param mixed $response
     * @return AbstractNLUResponse
     */
    abstract public function createResponse($response): AbstractNLUResponse;
}
