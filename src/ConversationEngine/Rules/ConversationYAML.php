<?php

namespace OpenDialogAi\ConversationEngine\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ConversationYAML implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $yaml = Yaml::parse($value);

            if (!isset($yaml['conversation']['scenes'])) {
                return false;
            }
        } catch (ParseException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid YAML';
    }
}
