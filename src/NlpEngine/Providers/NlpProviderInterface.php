<?php

namespace OpenDialogAi\NlpEngine\Providers;

use OpenDialogAi\Core\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\NlpEngine\NlpSentiment;

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
