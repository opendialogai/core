<?php

namespace OpenDialogAi\Core\NlpEngine\Client;

use Psr\Http\Message\StreamInterface;

/**
 * Class MsClient
 *
 * @package OpenDialogAi\Core\NlpEngine\Client
 */
class MsClient
{
    /** @var \GuzzleHttp\Client  */
    private $client;

    public function __construct()
    {
        $this->client = app()->make('MsClient');
    }

    /**
     * @param string $string
     * @param string $languageHint
     * @return array
     */
    public function getLanguage(string $string, string $languageHint): array
    {
        $body = [
            'documents' => [
                [
                    'countryHint' => $languageHint,
                    'id' => '1', // for now we set this to 1 as we aren't passing an array
                    'text' => $string,
                ],
            ],
        ];

        $response = $this->client->post(
            '/languages',
            [
                'form_params' => $body
            ]
        );

        return $this->formatResponse($response->getBody());
    }

    private function formatResponse(StreamInterface $response)
    {
        return json_decode($response->getContents(), true)['documents'][0]['detectedLanguages'];
    }
}
