<?php

namespace OpenDialogAi\InterpreterEngine\QnA;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class QnAClient
{
    /** @var Client */
    private $client;

    /** @var string */
    private $appUrl;

    /** @var string */
    private $endpointKey;

    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->appUrl = $config['app_url'];
        $this->endpointKey = $config['endpoint_key'];
    }

    /**
     * @param string $question
     * @return QnAResponse
     */
    public function queryQnA($question): QnAResponse
    {
        try {
            $query = $this->client->request(
                'POST',
                $this->appUrl,
                [
                    'headers' => [
                        'Authorization' => 'EndpointKey ' . $this->endpointKey,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'question' => $question,
                    ]),
                ]
            );
        } catch (\Exception $e) {
            Log::error(sprintf('QnA Error %s', $e->getMessage()));
            throw new QnARequestFailedException($e->getMessage());
        } catch (GuzzleException $e) {
            Log::error(sprintf('QnA Error %s', $e->getMessage()));
            throw new QnARequestFailedException($e->getMessage());
        }

        if ($query->getStatusCode() == '200') {
            $response = $query->getBody()->getContents();
            Log::debug('Successful QnA call', ['response' => $response]);
            return new QnAResponse(json_decode($response));
        }

        $response = $query->getBody()->getContents();
        Log::warning('Unsuccessful QnA call', ['response' => $response]);
        throw new QnARequestFailedException('QnA call failed with a non 200 response, please check the logs');
    }
}
