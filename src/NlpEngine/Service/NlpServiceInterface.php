<?php

namespace OpenDialogAi\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\NlpLanguage;

/**
 * Interface NlpServiceInterface
 *
 * @package OpenDialogAi\NlpEngine\Service
 */
interface NlpServiceInterface
{
    public function getLanguage(): NLPLanguage;
}
