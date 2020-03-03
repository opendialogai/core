<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use GuzzleHttp\Client;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUHTTPClient;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;
use Psr\Http\Message\ResponseInterface;

class RasaClient extends AbstractNLUHTTPClient
{
    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->appUrl = $config['app_url'];
    }

    /**
     * @inheritDoc
     */
    public function sendRequest($message): ResponseInterface
    {
        return $this->client->request(
            'POST',
            $this->appUrl . '/model/parse',
            [
                'body' => json_encode(['text' => $message])
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createResponse($response): AbstractNLUResponse
    {
        return new RasaResponse(json_decode($response));
    }
}
