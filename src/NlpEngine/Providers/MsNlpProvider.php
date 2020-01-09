<?php

namespace OpenDialogAi\Core\NlpEngine\Providers;

use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\NlpEngine\NlpSentiment;
use OpenDialogAi\NlpEngine\Providers\NlpProviderInterface;

class MsNlpProvider implements NlpProviderInterface
{
    /** @var \OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient  */
    private $client;

    /** @var string  */
    private $string;

    /** @var string  */
    private $defaultLanguage;

    /** @var string  */
    private $defaultCountryCode;

    /**
     * MsNlpProvider constructor.
     *
     * @param string                                                    $string
     * @param \OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient $client
     */
    public function __construct(string $string, MsClient $client)
    {
        $this->client = $client;
        $this->string = $string;
        $this->defaultLanguage = config('opendialog.nlp_engine.default_language') ? config(
            'opendialog.nlp_engine.default_language'
        ) : 'en';
        $this->defaultCountryCode = config('opendialog.nlp_engine.default_country_code') ? config(
            'opendialog.nlp_engine.default_country_code'
        ) : 'GB';
    }

    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpLanguage
     */
    public function getLanguage(): NLPLanguage
    {
        $nplLanguage = $this->client->getLanguage($this->string, $this->defaultCountryCode);

        $language = new NlpLanguage();
        $language->setInput($this->string);
        $language->setLanguageName($nplLanguage->getLanguageName());
        $language->setIsoName($nplLanguage->getIsoName());
        $language->setScore($nplLanguage->getScore());

        return $language;
    }

    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpSentiment
     */
    public function getSentiment(): NlpSentiment
    {
        $sentiment = $this->client->getSentiment($this->string, $this->defaultLanguage);

        return $sentiment;
    }

    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpEntities
     */
    public function getEntities(): NlpEntities
    {
        $entities = $this->client->getEntities($this->string, $this->defaultLanguage);

        return $entities;
    }
}
