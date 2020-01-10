<?php


namespace OpenDialogAi\NlpEngine\Providers;


use OpenDialogAi\Core\Traits\HasName;
use OpenDialogAi\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\NlpEngine\NlpEntities;
use OpenDialogAi\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\NlpSentiment;

abstract class AbstractNlpProvider implements NlpProviderInterface
{
    use HasName;

    protected static $name = '';

    /**
     * @inheritDoc
     */
    public function getLanguage(string $document): NlpLanguage
    {
        throw new NlpProviderMethodNotSupportedException();
    }

    /**
     * @inheritDoc
     */
    public function getEntities(string $document): NlpEntities
    {
        throw new NlpProviderMethodNotSupportedException();
    }

    /**
     * @inheritDoc
     */
    public function getSentiment(string $document): NlpSentiment
    {
        throw new NlpProviderMethodNotSupportedException();
    }
}
