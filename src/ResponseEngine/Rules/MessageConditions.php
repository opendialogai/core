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
                // See what condition our condition is in.
                if (empty($condition['condition'])) {
                    return false;
                }

                // Each condition must have at least an operation and an attribute.
                if (count($condition['condition']) < 2) {
                    return false;
                }

                // Each condition must have an operation.
                if (!isset($condition['condition']['operation']) || empty($condition['condition']['operation'])) {
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
