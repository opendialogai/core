<?php

namespace OpenDialogAi\Core\NlpEngine\Tests\Providers;

use OpenDialogAi\Core\NlpEngine\NlpSummary;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;
use OpenDialogAi\NlpEngine\Providers\AbstractNlpProvider;

class BadlyNamedProvider extends AbstractNlpProvider
{
    public static $name = 'bad';

    /**
     * @param string $document
     * @return NlpLanguage
     */
    public function getLanguage(string $document): NlpLanguage
    {
        // TODO: Implement getLanguage() method.
    }

    /**
     * @param string $document
     * @return NlpEntities
     */
    public function getEntities(string $document): NlpEntities
    {
        // TODO: Implement getEntities() method.
    }

    /**
     * @param string $document
     * @return NlpSentiment
     */
    public function getSentiment(string $document): NlpSentiment
    {
        // TODO: Implement getSentiment() method.
    }

    /**
     * @param string $document
     * @return NlpSummary
     */
    public function getSummary(string $document): NlpSummary
    {
        // TODO: Implement getSummary() method.
    }
}
