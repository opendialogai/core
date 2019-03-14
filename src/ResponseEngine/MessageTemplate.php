<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Symfony\Component\Yaml\Yaml;

/**
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $outgoing_intent_id
 * @property String $name
 * @property String $conditions
 * @property String $message_markup
 */
class MessageTemplate extends Model
{
    protected $fillable = [
        'name',
        'conditions',
        'message_markup',
        'outgoing_intent_id',
    ];

    /**
     * Get the outgoing intent that owns the message template.
     */
    public function outgoingIntent()
    {
        return $this->belongsTo('OpenDialogAi\ResponseEngine\OutgoingIntent');
    }

    /**
     * Helper method: return an array of conditions.
     */
    public function getConditions()
    {
        $yaml = Yaml::parse($this->conditions);
        if (!empty($yaml['conditions']) && is_array($yaml['conditions'])) {
            return $yaml['conditions'];
        }
        return [];
    }

    /**
     * Helper method: return an array of messages.
     */
    public function getMessages()
    {
        try {
            $message = new SimpleXMLElement($this->message_markup);
            $messages = [];
            foreach ($message->children() as $item) {
                switch ($item->getName()) {
                    case 'button-message':
                        $messages[] = $this->generateButtonMessage($item);
                        break;
                    case 'image-message':
                        $messages[] = $this->generateImageMessage($item);
                        break;
                    case 'text-message':
                        $messages[] = $this->generateTextMessage((string)$item);
                        break;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Message Builder error: ' . $e->getMessage());
            return false;
        }

    }

    /**
     * @param $text
     * @return WebChatMessage
     */
    protected function generateTextMessage($text)
    {
        $text = $this->fillSlots($text);
        return (new WebChatMessage())->setText($text);
    }
    /**
     * @param $item
     * @return WebChatButtonMessage
     */
    protected function generateButtonMessage($item)
    {
        $text = $this->fillSlots((string)$item->text);
        $message = new WebChatButtonMessage();
        $message->setText($text);
        foreach ($item->button as $button) {
            $buttonText = $this->fillSlots((string)$button->text);
            $message->addButton(new WebChatButton($buttonText, (string)$button->callback, (string)$button->value));
        }
        return $message;
    }
    /**
     * @param $item
     * @return WebChatImageMessage
     */
    protected function generateImageMessage($item)
    {
        return (new WebChatImageMessage())->setImgSrc((string)$item->src)->setImgLink((string)$item->link);
    }
}
