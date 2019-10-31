<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RasaClient
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
     * @param $message
     * @return RasaResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryRasa($message)
    {
        try {
            $query = $this->client->request(
                'POST',
                $this->appUrl . '/model/parse',
                [
                    'body' => json_encode(['text' => $message])
                ]
            );
        } catch (\Exception $e) {
            Log::error(sprintf("RASA Error %s", $e->getMessage()));
            throw new RasaRequestFailedException($e->getMessage());
        }

        if ($query->getStatusCode() == '200') {
            $response = $query->getBody()->getContents();
            Log::debug("Successful RASA call", ['response' => $response]);
            return new RasaResponse(json_decode($response));
        } else {
            $response = $query->getBody()->getContents();
            Log::warning("Unsuccessful RASA call", ['response' => $response]);
            throw new RasaRequestFailedException("RASA call failed with a non 200 response, please check the logs");
        }
    }
}
