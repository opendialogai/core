<?php

namespace OpenDialogAi\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\NlpEntities;
use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\Core\NlpEngine\NlpSentiment;

interface NlpServiceInterface
{
    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpLanguage
     */
    public function getLanguage(): NlpLanguage;

    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpEntities
     */
    public function getEntities(): NlpEntities;

    /**
     * @return \OpenDialogAi\Core\NlpEngine\NlpSentiment
     */
    public function getSentiment(): NlpSentiment;
}
