<?php

namespace OpenDialogAi\Core\NlpEngine\tests\Providers;

use OpenDialogAi\Core\NlpEngine\NlpSummary;
use OpenDialogAi\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;
use OpenDialogAi\NlpEngine\Providers\AbstractNlpProvider;

class NoMethods extends AbstractNlpProvider
{
    public static $name = 'nlp_provider.test.no_methods';

    /**
     * @param string $document
     * @return NlpLanguage
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getLanguage(string $document): NlpLanguage
    {
        throw new NlpProviderMethodNotSupportedException();
    }

    /**
     * @param string $document
     * @return NlpEntities
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getEntities(string $document): NlpEntities
    {
        throw new NlpProviderMethodNotSupportedException();
    }

    /**
     * @param string $document
     * @return NlpSentiment
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getSentiment(string $document): NlpSentiment
    {
        throw new NlpProviderMethodNotSupportedException();
    }

    /**
     * @param string $document
     * @return NlpSummary
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getSummary(string $document): NlpSummary
    {
        throw new NlpProviderMethodNotSupportedException();
    }
}
