<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractNLUHTTPClient extends AbstractNLUClient
{
    /** @var Client */
    protected $client;

    /** @var string The app specific URL */
    protected $appUrl;

    /**
     * @param Client $client
     * @param $config
     */
    abstract public function __construct(Client $client, $config);

    /**
     * @param $message
     * @return mixed
     */
    abstract public function sendRequest($message): ResponseInterface;

    /**
     * Sends a message to the NLU service and creates a response object
     * @param $message
     * @return AbstractNLUResponse
     * @throws AbstractNLURequestFailedException
     */
    public function query($message): AbstractNLUResponse
    {
        try {
            $query = $this->sendRequest($message);
        } catch (Exception $e) {
            throw new AbstractNLURequestFailedException($e->getMessage());
        }

        $responseData = $query->getBody()->getContents();
        if ($query->getStatusCode() == '200') {
            Log::debug("Successful client call", ['response' => $responseData]);
            return $this->createResponse($responseData);
        } else {
            Log::warning("Unsuccessful client call", ['response' => $responseData]);
            throw new AbstractNLURequestFailedException("Client call failed with a non 200 response, please check the logs");
        }
    }
}
