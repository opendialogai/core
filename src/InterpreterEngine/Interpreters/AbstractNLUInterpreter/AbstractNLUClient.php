<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

abstract class AbstractNLUClient
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
     * @throws GuzzleException
     */
    abstract public function sendRequest($message): Response;

    /**
     * @param mixed $response
     * @return AbstractNLUResponse
     */
    abstract public function createResponse($response): AbstractNLUResponse;

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
        } catch (GuzzleException $e) {
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
