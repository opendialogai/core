<?php

namespace OpenDialogAi\ResponseEngine\Rules;

use OpenDialogAi\Core\Rules\BaseRule;
use SimpleXMLElement;

class MessageXML extends BaseRule
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
            // Replace unescaped ampersands with &amp;
            $value = preg_replace('/&(?!;{6})/', '&amp;', $value);

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
                        foreach ($item->button as $button) {
                            $buttonXml = $button->text->asXml();
                            // Remove button text enclosing tags.
                            $buttonText = preg_replace('/<text[^>]*>/', '', $buttonXml);
                            $buttonText = trim(preg_replace('/<\/text>/', '', $buttonText));

                            if (empty((string)$button->callback) && empty((string)$button->download)
                                && empty((string)$button->tab_switch) && empty((string)$button->link)
                                && empty((string)$button->click_to_call)) {
                                // @codingStandardsIgnoreLine
                                $this->setErrorMessage('All buttons must have with a "callback", "download", "link", "tab_switch" or "click_to_call" set');
                                return false;
                            }
                            if (empty($buttonText)) {
                                $this->setErrorMessage('Button must have "text"');
                                return false;
                            }
                            if (strip_tags($buttonText, '<b><i><u>') !== $buttonText) {
                                $this->setErrorMessage('Button text contains an invalid tag (allowed tags are <b>, <i> and <u>');
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
                            if (empty((string)$button->callback) && empty((string)$button->download)
                                && empty((string)$button->tab_switch) && empty((string)$button->link)) {
                                $this->setErrorMessage(
                                    'All buttons must have with a "callback", "download", "link" or "tab_switch" set'
                                );
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

                    case 'fp-rich-message':
                        if (empty((string)$item->text)) {
                            $this->setErrorMessage('Full page rich messages must have "text"');
                            return false;
                        }
                        if ($item->button->count() > 4) {
                            $this->setErrorMessage('Full page rich messages can only have up to 4 buttons');
                            return false;
                        }
                        foreach ($item->button as $button) {
                            if (empty((string)$button->text)) {
                                $this->setErrorMessage('Full page rich message buttons must have "text"');
                                return false;
                            }
                            if (empty((string)$button->callback) && empty((string)$button->download)
                                && empty((string)$button->tab_switch) && empty((string)$button->link)) {
                                $this->setErrorMessage(
                                    'All buttons must have with a "callback", "download", "link" or "tab_switch" set'
                                );
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

                    case 'fp-form-message':
                    case 'form-message':
                        if (empty((string)$item->text)) {
                            $this->setErrorMessage('Form messages must have "text"');
                            return false;
                        }
                        foreach ($item->element as $element) {
                            if (empty((string)$element->element_type)) {
                                $this->setErrorMessage('Form message elements must have "element_type"');
                                return false;
                            }
                            if (empty((string)$element->name)) {
                                $this->setErrorMessage('Form message elements must have "name"');
                                return false;
                            }
                            if ((string)$element->element_type == 'select' ||
                                (string)$element->element_type == 'auto_complete_select') {
                                if (empty((string)$element->options)) {
                                    // @codingStandardsIgnoreLine
                                    $this->setErrorMessage('Form message elements of type "select" or "auto_complete_select" must have "options"');
                                    return false;
                                }
                            }
                            if ((string)$element->element_type == 'radio') {
                                if (empty((string)$element->options)) {
                                    // @codingStandardsIgnoreLine
                                    $this->setErrorMessage('Form message elements of type "radio" must have "options"');
                                    return false;
                                }
                            }
                        }
                        break;

                    case 'hand-to-system-message':
                        if ((string)$item->attributes()['system'] == '') {
                            $this->setErrorMessage('Hand to system messages must have a non-empty "system" attribute.');
                            return false;
                        }
                        break;

                    case 'empty-message':
                    case 'meta-message':
                    case 'long-text-message':
                    case 'cta-message':
                    case 'autocomplete-message':
                    case 'date-picker-message':
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
}
