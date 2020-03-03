<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

interface AbstractNLUClientInterface
{
    /**
     * @param $message
     * @return mixed
     */
    public function sendRequest($message);

    /**
     * @param mixed $response
     * @return AbstractNLUResponse
     */
    public function createResponse($response): AbstractNLUResponse;

    /**
     * Sends a message to the NLU service and creates a response object
     * @param $message
     * @return AbstractNLUResponse
     * @throws AbstractNLURequestFailedException
     */
    public function query($message): AbstractNLUResponse;
}
