<?php


namespace OpenDialogAi\Core\NlpEngine\Providers;


use OpenDialogAi\Core\NlpEngine\Exceptions\NlpProviderMethodNotSupportedException;
use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\NlpEngine\NlpSentiment;
use OpenDialogAi\Core\Traits\HasName;
use OpenDialogAi\NlpEngine\Providers\NlpProviderInterface;

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
