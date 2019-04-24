<?php

namespace OpenDialogAi\ResponseEngine\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use SimpleXMLElement;

class MessageXML implements Rule
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
            $message = new SimpleXMLElement($value);

            foreach ($message->children() as $item) {
                switch ($item->getName()) {
                    case 'attribute-message':
                        if (empty((string)$item)) {
                            return false;
                        }
                        break;

                    case 'text-message':
                        if (empty((string)$item)) {
                            return false;
                        }
                        break;

                    case 'button-message':
                        if (empty((string)$item->text)) {
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->text) || empty((string)$button->callback)) {
                                return false;
                            }
                        }
                        break;

                    case 'image-message':
                        if (empty((string)$item->src)) {
                            return false;
                        }
                        break;
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
        return 'Invalid message mark up.';
    }
}
