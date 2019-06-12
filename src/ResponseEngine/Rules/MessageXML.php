<?php

namespace OpenDialogAi\ResponseEngine\Rules;

use Illuminate\Contracts\Validation\Rule;
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
                        $attributeValid = false;

                        if (empty((string)$item)) {
                            foreach ($item->children() as $child) {
                                if (!empty((string) $child)) {
                                    $attributeValid = true;
                                }
                            }
                        } else {
                            $attributeValid = true;
                        }

                        if (!$attributeValid) {
                            return false;
                        }

                        break;

                    case 'text-message':
                        if (empty((string)$item) && empty($item->link)) {
                            return false;
                        }
                        foreach ($item->link as $link) {
                            if (empty((string)$link->url) || empty((string)$link->text)) {
                                return false;
                            }
                        }
                        break;

                    case 'button-message':
                        if (empty((string)$item->text)) {
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->callback) && empty((string)$button->tab_switch)) {
                                return false;
                            }

                            if (empty((string)$button->text)) {
                                return false;
                            }
                        }
                        break;

                    case 'image-message':
                        if (empty((string)$item->src)) {
                            return false;
                        }
                        break;

                    case 'rich-message':
                        if (empty((string)$item->text)) {
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->text)) {
                                return false;
                            }
                            if (empty((string)$button->callback) && empty((string)$button->link)) {
                                return false;
                            }
                        }
                        foreach ($item->image as $image) {
                            if (empty((string)$image->src)) {
                                return false;
                            }
                        }
                        break;

                    case 'list-message':
                        foreach ($item->item as $i => $item) {
                            if ($this->passes($attribute, $item->asXML()) === false) {
                                return false;
                            }
                        }
                        break;

                    case 'form-message':
                        if (empty((string)$item->text)) {
                            return false;
                        }
                        break;

                    case 'long-text-message':
                        break;

                    default:
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
        return 'Invalid message mark up.';
    }
}
