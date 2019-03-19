<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
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
 * @property OutgoingIntent $outgoing_intent
 */
class MessageTemplate extends Model
{
    /** @var AttributeResolverService */
    protected $attributeResolver;

    protected $fillable = [
        'name',
        'conditions',
        'message_markup',
        'outgoing_intent_id',
    ];

    /**
     * MessageTemplate constructor.
     * @param array $attributes
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributeResolver = app()->make(AttributeResolverService::ATTRIBUTE_RESOLVER);
    }

    /**
     * Get the outgoing intent that owns the message template.
     */
    public function outgoingIntent()
    {
        return $this->belongsTo('OpenDialogAi\ResponseEngine\OutgoingIntent');
    }

    /**
     * Scope a query by intent ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $intentName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForIntent($query, $intentName)
    {
        return $query
            ->join('outgoing_intents', 'outgoing_intents.id', '=', 'message_templates.outgoing_intent_id')
            ->where('outgoing_intents.name', $intentName);
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

        return $messages;
    }

    /**
     * @param $text
     * @return WebChatMessage
     */
    protected function generateTextMessage($text)
    {
        $text = $this->fillAttributes($text);
        return (new WebChatMessage())->setText($text);
    }

    /**
     * @param $item
     * @return WebChatButtonMessage
     */
    protected function generateButtonMessage($item)
    {
        $text = $this->fillAttributes((string) $item->text);
        $message = new WebChatButtonMessage();
        $message->setText($text);
        foreach ($item->button as $button) {
            $buttonText = $this->fillAttributes((string) $button->text);
            $message->addButton(new WebChatButton($buttonText, (string) $button->callback, (string) $button->value));
        }
        return $message;
    }

    /**
     * @param $item
     * @return WebChatImageMessage
     */
    protected function generateImageMessage($item)
    {
        return (new WebChatImageMessage())->setImgSrc((string) $item->src)->setImgLink((string) $item->link);
    }

    /**
     * @param $text
     * @return string
     */
    protected function fillAttributes($text)
    {
        foreach ($this->attributeResolver->getAvailableAttributes() as $attributeName => $attributeClass) {
            $value = $this->attributeResolver->getAttributeFor($attributeName)->getValue();
            $text = str_replace('{' . $attributeName . '}', $value, $text);
        }
        return $text;
    }
}
