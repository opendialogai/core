<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

use Exception;
use Illuminate\Support\Facades\Log;

abstract class AbstractNLUCustomClient extends AbstractNLUClient
{
    /**
     * @param $message
     * @return AbstractNLUCustomRequest
     */
    abstract public function sendRequest($message): AbstractNLUCustomRequest;

    /**
     * Sends a message to the NLU service and creates a response object
     * @param $message
     * @return AbstractNLUResponse
     * @throws AbstractNLURequestFailedException
     */
    public function query($message): AbstractNLUResponse
    {
        try {
            $request = $this->sendRequest($message);
        } catch (Exception $e) {
            throw new AbstractNLURequestFailedException($e->getMessage());
        }

        $responseData = $request->getContents();
        if ($request->isSuccessful()) {
            Log::debug("Successful client call", ['response' => $responseData]);
            return $this->createResponse($responseData);
        } else {
            Log::warning("Unsuccessful client call", ['response' => $responseData]);
            throw new AbstractNLURequestFailedException("Client call failed with a non 200 response, please check the logs");
        }
    }
}
