<?php

namespace OpenDialogAi\ConversationEngine\Rules;

use OpenDialogAi\Core\Rules\BaseRule;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ConversationYAML extends BaseRule
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

            if (!isset($yaml['conversation']['id'])) {
                $this->setErrorMessage('Conversation have must an ID');
                return false;
            }

            if (!isset($yaml['conversation']['scenes'])) {
                $this->setErrorMessage('Conversation must have at least 1 scene');
                return false;
            }
        } catch (ParseException $e) {
            $this->setErrorMessage(sprintf('Invalid YAML - %s', $e->getMessage()));
            return false;
        }

        return true;
    }
}
