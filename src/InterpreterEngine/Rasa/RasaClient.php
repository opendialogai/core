<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUClient;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class RasaClient extends AbstractNLUClient
{
    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->appUrl = $config['app_url'];
    }

    /**
     * @inheritDoc
     */
    public function sendRequest($message): Response
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
        return new RasaResponse($response);
    }
}
