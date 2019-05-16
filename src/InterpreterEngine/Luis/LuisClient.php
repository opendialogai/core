<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class LuisClient
{
    /** @var Client */
    private $client;

    /** @var string The app specific URL */
    private $appUrl;

    /** @var string The app ID */
    private $appId;

    /** @var string */
    private $subscriptionKey;

    /** @var string */
    private $staging;

    private $timezoneOffset;

    /** @var string */
    private $verbose;

    /** @var string */
    private $spellCheck;

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
     * @param $message
     * @return LuisResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryLuis($message)
    {
        try {
            $query = $this->client->request(
                'GET',
                $this->appUrl . '/' . $this->appId,
                [
                    'query' =>
                        [
                            'staging' => $this->staging,
                            'timezone-offset' => $this->timezoneOffset,
                            'verbose' => $this->verbose,
                            'spellcheck' => $this->spellCheck,
                            'subscription-key' => $this->subscriptionKey,
                            'q' => $message
                        ],
                ]
            );
        } catch (\Exception $e) {
            Log::error(sprintf("LUIS Error %s", $e->getMessage()));
            throw new LuisRequestFailedException($e->getMessage());
        }

        if ($query->getStatusCode() == '200') {
            $response = $query->getBody()->getContents();
            Log::debug("Successful LUIS call", ['response' => $response]);
            return new LuisResponse(json_decode($response));
        } else {
            $response = $query->getBody()->getContents();
            Log::warning("Unsuccessful LUIS call", ['response' => $response]);
            throw new LuisRequestFailedException("LUIS call failed with a non 200 response, please check the logs");
        }
    }
}
