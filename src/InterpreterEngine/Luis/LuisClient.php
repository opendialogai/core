<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

use GuzzleHttp\Client;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUHTTPClient;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;
use Psr\Http\Message\ResponseInterface;

class LuisClient extends AbstractNLUHTTPClient
{
    /** @var string The app ID */
    private $appId;

    /** @var string */
    private $subscriptionKey;

    /** @var string */
    private $staging;

    /** @var string */
    private $timezoneOffset;

    /** @var string */
    private $verbose;

    /** @var string */
    private $spellCheck;

    /**
     * @inheritDoc
     */
    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->appUrl = $config['app_url'];
        $this->appId = $config['app_id'];
        $this->staging = $config['staging'] ? 'TRUE' : 'FALSE';
        $this->subscriptionKey = $config['subscription_key'];
        $this->timezoneOffset = $config['timezone_offset'];
        $this->verbose = $config['verbose'] ? 'TRUE' : 'FALSE';
        $this->spellCheck = $config['spellcheck'] ? 'TRUE' : 'FALSE';
    }

    /**
     * @inheritDoc
     */
    public function sendRequest($message): ResponseInterface
    {
        return $this->client->request(
            'GET',
            $this->appUrl . '/' . $this->appId,
            [
                'query' => [
                    'staging' => $this->staging,
                    'timezone-offset' => $this->timezoneOffset,
                    'verbose' => $this->verbose,
                    'spellcheck' => $this->spellCheck,
                    'subscription-key' => $this->subscriptionKey,
                    'q' => $message
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createResponse($response): AbstractNLUResponse
    {
        return new LuisResponse(json_decode($response));
    }
}
