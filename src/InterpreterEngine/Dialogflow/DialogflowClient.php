<?php

namespace OpenDialogAi\InterpreterEngine\DialogFlow;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUClient;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class DialogflowClient extends AbstractNLUClient
{
    protected $appUrl;

    /**
     * @inheritDoc
     */
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
            'GET',
            $this->appUrl . '/',
            [
                'query' => [
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createResponse($response): AbstractNLUResponse
    {
        return true;
    }
}
