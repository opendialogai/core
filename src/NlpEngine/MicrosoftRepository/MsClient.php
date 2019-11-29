<?php

namespace OpenDialogAi\Core\NlpEngine\MicrosoftRepository;

use OpenDialogAi\Core\NlpEngine\NlpSentiment;

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
     * @return MsLanguageEntity
     */
    public function getLanguage(string $string, string $languageHint): MsLanguageEntity
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

        return new MsLanguageEntity($response);
    }

    /**
     * @param string $string
     * @param string $language
     * @return \OpenDialogAi\Core\NlpEngine\NlpSentiment
     */
    public function getSentiment(string $string, string $language): NlpSentiment
    {
        $body = [
            'documents' => [
                [
                    'language' => $language,
                    'id' => '1', // for now we set this to 1 as we aren't passing an array
                    'text' => $string,
                ],
            ],
        ];

        $response = $this->client->post(
            '/sentiment',
            [
                'form_params' => $body
            ]
        );

        $entity = json_decode($response->getBody()->getContents(), true)['documents'][0];

        $nlpSentiment = new NlpSentiment();
        $nlpSentiment->setInput($string);
        $nlpSentiment->setScore($entity['score']);

        return $nlpSentiment;
    }
}
