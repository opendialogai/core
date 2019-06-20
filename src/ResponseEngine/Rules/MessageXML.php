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
                            $this->setErrorMessage('Invalid attribute message structure');
                            return false;
                        }

                        break;

                    case 'text-message':
                        if (empty((string)$item) && empty($item->link)) {
                            $this->setErrorMessage('Text messages must have "text"');
                            return false;
                        }
                        foreach ($item->link as $link) {
                            if (empty((string)$link->url) || empty((string)$link->text)) {
                                $this->setErrorMessage('Text message links require "text" and "url"');
                                return false;
                            }
                        }
                        break;

                    case 'button-message':
                        if (empty((string)$item->text)) {
                            $this->setErrorMessage('Button messages must have "text"');
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->callback) && empty((string)$button->tab_switch)
                                && empty((string)$button->link)) {
                                $this->setErrorMessage('All buttons must have with a "callback", "link" or "tab_switch" set');
                                return false;
                            }
                            if (empty((string)$button->text)) {
                                $this->setErrorMessage('Button must have "text"');
                                return false;
                            }
                        }
                        break;

                    case 'image-message':
                        if (empty((string)$item->src)) {
                            $this->setErrorMessage('Image messages must have a "src"');
                            return false;
                        }
                        break;

                    case 'rich-message':
                        if (empty((string)$item->text)) {
                            $this->setErrorMessage('Rich messages must have "text"');
                            return false;
                        }
                        if ($item->button->count() > 3) {
                            $this->setErrorMessage('Rich messages can only have up to 3 buttons');
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->text)) {
                                $this->setErrorMessage('Rich message buttons must have "text"');
                                return false;
                            }
                            if (empty((string)$button->callback) && empty((string)$button->tab_switch)
                                && empty((string)$button->link)) {
                                $this->setErrorMessage('All buttons must have with a "callback", "link" or "tab_switch" set');
                                return false;
                            }
                        }
                        foreach ($item->image as $image) {
                            if (empty((string)$image->src)) {
                                $this->setErrorMessage('Images must have a "src"');
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
                            $this->setErrorMessage('Form messages must have "text"');
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
            $this->setErrorMessage(sprintf('Message XML structure is invalid - %s', $e->getMessage()));
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
