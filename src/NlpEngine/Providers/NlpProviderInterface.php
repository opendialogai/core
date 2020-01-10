<?php

namespace OpenDialogAi\NlpEngine\Providers;

use OpenDialogAi\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;

interface NlpProviderInterface
{
    /**
     * @param string $document
     * @return NlpLanguage
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getLanguage(string $document): NlpLanguage;

    /**
     * @param string $document
     * @return NlpEntities
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getEntities(string $document): NlpEntities;

    /**
     * @param string $document
     * @return NlpSentiment
     * @throws NlpProviderMethodNotSupportedException
     */
    public function getSentiment(string $document): NlpSentiment;
}
