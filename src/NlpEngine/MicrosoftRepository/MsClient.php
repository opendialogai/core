<?php

namespace OpenDialogAi\NlpEngine\MicrosoftRepository;

use GuzzleHttp\Client;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpEntity;
use OpenDialogAi\NlpEngine\NlpEntityMatch;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;

class MsClient
{
    /** @var Client */
    private $client;

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function __construct(Client $client)
    {
        $this->client = $client;
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
     * @return \OpenDialogAi\NlpEngine\NlpSentiment
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
     * @param \OpenDialogAi\NlpEngine\NlpEntity $nlpEntity
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
