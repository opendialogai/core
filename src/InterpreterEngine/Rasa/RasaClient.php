<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use OpenDialogAi\InterpreterEngine\Luis\AbstractNLUClient;
use OpenDialogAi\InterpreterEngine\Luis\AbstractNLUResponse;

class RasaClient extends AbstractNLUClient
{
    /** @var Client */
    private $client;

    /** @var string The app specific URL */
    private $appUrl;

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
     * @param mixed $response
     * @return AbstractNLUResponse
     */
    public function createResponse($response): AbstractNLUResponse
    {
        return new RasaResponse($response);
    }
}
