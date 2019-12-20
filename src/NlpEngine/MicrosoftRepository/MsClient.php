<?php

namespace OpenDialogAi\Core\NlpEngine\MicrosoftRepository;

use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpEntity;
use OpenDialogAi\Core\NlpEngine\NlpEntityMatch;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\NlpEngine\NlpSentiment;

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
     * @return NlpLanguage
     */
    public function getLanguage(string $string, string $languageHint): NlpLanguage
    {
        $body = [
            'documents' => [
                [
                    'countryHint' => '',
                    'id' => '1', // for now we set this to 1 as we aren't passing an array
                    'text' => $string,
                ],
            ],
        ];
        $response = $this->client->post(
            'languages',
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($body)
            ]
        );
        $language = json_decode($response->getBody()->getContents(), true)['documents'][0]['detectedLanguages'][0];

        $nlpLanguage = new NlpLanguage();
        $nlpLanguage->setInput($string);
        $nlpLanguage->setScore($language['score']);
        $nlpLanguage->setLanguageName($language['name']);
        $nlpLanguage->setIsoName($language['iso6391Name']);
        return $nlpLanguage;
    }

    /**
     * @param string $string
     * @param string $language
     * @return \OpenDialogAi\Core\NlpEngine\NlpSentiment
     */
    public function getSentiment(string $string, string $language): NlpSentiment
    {
        $body = $this->getRequestBody($string, $language);

        $response = $this->client->post(
            'sentiment',
            [
                'body' => json_encode($body)
            ]
        );

        $entity = json_decode($response->getBody()->getContents(), true)['documents'][0];

        $nlpSentiment = new NlpSentiment();
        $nlpSentiment->setInput($string);
        $nlpSentiment->setScore($entity['score']);

        return $nlpSentiment;
    }

    public function getEntities(string $string, string $language): NlpEntities
    {
        $body = $this->getRequestBody($string, $language);

        $response = $this->client->post(
            'entities',
            [
                'body' => json_encode($body)
            ]
        );

        $entity = json_decode($response->getBody()->getContents(), true)['documents'][0];

        $nlpEntities = new NlpEntities();
        $nlpEntities->setInput($string);

        foreach ($entity['entities'] as $entity) {
            $nlpEntity = new NlpEntity();
            $nlpEntity->setInput($string);
            $nlpEntity->setName($entity['name']);
            $nlpEntity->setType($entity['type']);

            // loop through the matches and assign
            $this->buildMatches($entity, $nlpEntity);

            $nlpEntities->addEntities($nlpEntity);
        }

        return $nlpEntities;
    }

    /**
     * @param string $string
     * @param string $language
     * @return array
     */
    private function getRequestBody(string $string, string $language): array
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

        return $body;
    }

    /**
     * @param                                        $entity
     * @param \OpenDialogAi\Core\NlpEngine\NlpEntity $nlpEntity
     */
    private function buildMatches($entity, NlpEntity $nlpEntity): void
    {
        foreach ($entity['matches'] as $match) {
            $nlpEntityMatch = new NlpEntityMatch();
            if (array_key_exists('wikipediaScore', $match)) {
                $nlpEntityMatch->setWikipediaScore($match['wikipediaScore']);
            }
            $nlpEntityMatch->setEntityTypeScore($match['entityTypeScore']);
            $nlpEntityMatch->setText($match['text']);

            $nlpEntity->addMatch($nlpEntityMatch);
        }
    }
}
