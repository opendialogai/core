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

    /** @var boolean */
    private $staging;

    private $timezoneOffset;

    /** @var bool */
    private $verbose;

    /** @var bool  */
    private $spellCheck;

    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->appUrl = $config['app_url'];
        $this->appId = $config['app_id'];
        $this->staging = $config['staging'];
        $this->subscriptionKey = $config['subscription_key'];
        $this->timezoneOffset = $config['timezone_offset'];
        $this->verbose = $config['verbose'];
        $this->spellCheck = $config['spellcheck'];
    }

    /**
     * @param $message
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function queryLUIS($message)
    {
        try {

            $query = $this->client->request('GET',
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
            Log::warning(sprintf("Successful LUIS call"), ['response' => $query->getBody()->getContents()]);
            return new LUISResponse(json_decode($query->getBody()->getContents()));
        } else {
            // TODO - can we handle this?
            Log::warning("Unsuccessful LUIS call", ['response' => $query->getBody()->getContents()]);
            return json_decode($query->getBody()->getContents());
        }
    }
}
