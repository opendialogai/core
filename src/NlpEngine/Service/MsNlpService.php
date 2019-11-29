<?php

namespace OpenDialogAi\Core\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\MicrosoftRepository\MsClient;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

/**
 * Class MsNlpService
 *
 * @package OpenDialogAi\Core\NlpEngine\Service
 */
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
        $msLanguageEntity = $this->client->getLanguage($this->string, self::LANGUAGE_DEFAULT);

        $language = new NlpLanguage();
        $language->setInput($this->string);
        $language->setLanguageName($msLanguageEntity->getName());
        $language->setIsoName($msLanguageEntity->getIsoName());
        $language->setScore($msLanguageEntity->getScore());

        return $language;
    }
}
