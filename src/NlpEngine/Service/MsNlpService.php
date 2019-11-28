<?php

namespace OpenDialogAi\Core\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\Client\MsClient;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

/**
 * Class MsNlpService
 *
 * @package OpenDialogAi\Core\NlpEngine\Service
 */
class MsNlpService implements NlpServiceInterface
{
    /** @var \OpenDialogAi\Core\NlpEngine\Client\MsClient  */
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
        $languageResponse = $this->client->getLanguage($this->string, self::LANGUAGE_DEFAULT);

        $language = new NlpLanguage();
        $language->setLanguageName($languageResponse[0]['name']);
        $language->setIsoName($languageResponse[0]['iso6391Name']);
        $language->setScore($languageResponse[0]['score']);

        return $language;
    }
}
