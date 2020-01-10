<?php

namespace OpenDialogAi\NlpEngine\Providers;

use OpenDialogAi\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;

class MsNlpProvider extends AbstractNlpProvider
{
    protected static $name = "nlp_provider.core.microsoft";

    /** @var \OpenDialogAi\NlpEngine\MicrosoftRepository\MsClient  */
    private $client;

    /** @var string  */
    private $defaultLanguage;

    /** @var string  */
    private $defaultCountryCode;

    /**
     * MsNlpProvider constructor.
     */
    public function __construct()
    {
        $this->client = resolve(MsClient::class);
        $this->defaultLanguage = config('opendialog.nlp_engine.default_language') ? config(
            'opendialog.nlp_engine.default_language'
        ) : 'en';
        $this->defaultCountryCode = config('opendialog.nlp_engine.default_country_code') ? config(
            'opendialog.nlp_engine.default_country_code'
        ) : 'GB';
    }

    /**
     * @param string $document
     * @return \OpenDialogAi\NlpEngine\NlpLanguage
     */
    public function getLanguage(string $document): NLPLanguage
    {
        $nplLanguage = $this->client->getLanguage($document, $this->defaultCountryCode);

        $language = new NlpLanguage();
        $language->setInput($document);
        $language->setLanguageName($nplLanguage->getLanguageName());
        $language->setIsoName($nplLanguage->getIsoName());
        $language->setScore($nplLanguage->getScore());

        return $language;
    }

    /**
     * @param string $document
     * @return \OpenDialogAi\NlpEngine\NlpSentiment
     */
    public function getSentiment(string $document): NlpSentiment
    {
        $sentiment = $this->client->getSentiment($document, $this->defaultLanguage);

        return $sentiment;
    }

    /**
     * @param string $document
     * @return \OpenDialogAi\NlpEngine\NlpEntities
     */
    public function getEntities(string $document): NlpEntities
    {
        $entities = $this->client->getEntities($document, $this->defaultLanguage);

        return $entities;
    }
}
