<?php

namespace OpenDialogAi\Core\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\NlpEngine\NlpSentiment;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

class MsNlpService implements NlpServiceInterface
{
    /** @var \OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient  */
    private $client;

    /** @var string  */
    private $string;

    const LANGUAGE_DEFAULT = 'GB';

    public function __construct(string $string, MsClient $client)
    {
        $this->client = $client;
        $this->string = $string;
    }

    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpLanguage
     */
    public function getLanguage(): NLPLanguage
    {
        $nplLanguage = $this->client->getLanguage($this->string, self::LANGUAGE_DEFAULT);

        $language = new NlpLanguage();
        $language->setInput($this->string);
        $language->setLanguageName($nplLanguage->getLanguageName());
        $language->setIsoName($nplLanguage->getIsoName());
        $language->setScore($nplLanguage->getScore());

        return $language;
    }

    public function getSentiment(): NlpSentiment
    {
        $sentiment = $this->client->getSentiment($this->string, 'en');

        return $sentiment;
    }

    public function getEntities(): NlpEntities
    {
        $entities = $this->client->getEntities($this->string, 'en');

        return $entities;
    }
}
