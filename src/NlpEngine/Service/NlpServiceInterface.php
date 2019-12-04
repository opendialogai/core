<?php

namespace OpenDialogAi\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\NlpLanguage;

interface NlpServiceInterface
{
    public function getLanguage(): NLPLanguage;
}
