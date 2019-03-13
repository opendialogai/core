<?php

namespace OpenDialogAi\ResponseEngine\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

class MessageConditions implements Rule
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
        // Empty conditions are allowed.
        if ($value == '') {
            return true;
        }

        try {
            $yaml = Yaml::parse($value);
            if (empty($yaml['conditions']) || !is_array($yaml['conditions'])) {
                return false;
            }

            foreach ($yaml['conditions'] as $condition) {
                // Each condition must have an operation and a value.
                if (count($condition) !== 2) {
                    return false;
                }

                // Each condition must have an operation.
                if (!isset($condition['operation']) || empty($condition['operation'])) {
                    return false;
                }
            }
        } catch (\Exception $e) {
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
        return 'Invalid condition found.';
    }
}
