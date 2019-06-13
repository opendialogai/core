<?php

namespace OpenDialogAi\ResponseEngine\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class MessageXML implements Rule
{
    private $errorMessage = 'Invalid message mark up.';

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
                            $this->setErrorMessage('Attribute not valid');
                            return false;
                        }

                        break;

                    case 'text-message':
                        if (empty((string)$item) && empty($item->link)) {
                            $this->setErrorMessage('Text messages must have text');
                            return false;
                        }
                        foreach ($item->link as $link) {
                            if (empty((string)$link->url) || empty((string)$link->text)) {
                                $this->setErrorMessage('Link require not empty "text" and "url" attributes');
                                return false;
                            }
                        }
                        break;

                    case 'button-message':
                        if (empty((string)$item->text)) {
                            $this->setErrorMessage('Button messages must have text');
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->callback) && empty((string)$button->tab_switch)) {
                                $this->setErrorMessage('Button require a not empty "callback" or "tab_switch" attribute');
                                return false;
                            }

                            if (empty((string)$button->text)) {
                                $this->setErrorMessage('Button require a not empty "text" attribute');
                                return false;
                            }
                        }
                        break;

                    case 'image-message':
                        if (empty((string)$item->src)) {
                            $this->setErrorMessage('Image messages must have src');
                            return false;
                        }
                        break;

                    case 'rich-message':
                        if (empty((string)$item->text)) {
                            $this->setErrorMessage('Rich messages must have text');
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->text)) {
                                $this->setErrorMessage('Button require a not empty "text" attribute');
                                return false;
                            }
                            if (empty((string)$button->callback) && empty((string)$button->link)) {
                                $this->setErrorMessage('Button require a not empty "callback" or "link" attribute');
                                return false;
                            }
                        }
                        foreach ($item->image as $image) {
                            if (empty((string)$image->src)) {
                                $this->setErrorMessage('Image require a not empty "src" attribute');
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
                            $this->setErrorMessage('Form messages must have text');
                            return false;
                        }
                        break;

                    case 'long-text-message':
                        break;

                    default:
                        $this->setErrorMessage('Unknown message type "' . $item->getName() . '"');
                        return false;
                }
            }
        } catch (\Exception $e) {
            $this->setErrorMessage('Insert a valid XML');
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
        return $this->errorMessage;
    }

    private function setErrorMessage($errorMessage)
    {
        Log::debug($errorMessage);
        $this->errorMessage = $errorMessage;
    }
}
