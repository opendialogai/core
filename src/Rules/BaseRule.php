<?php

namespace OpenDialogAi\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

abstract class BaseRule implements Rule
{
    private $errorMessage = 'Invalid mark up.';

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }

    protected function setErrorMessage($errorMessage)
    {
        Log::info($errorMessage);
        $this->errorMessage = $errorMessage;
    }
}
