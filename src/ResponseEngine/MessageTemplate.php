<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
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
        $conditions = [];

        $yaml = Yaml::parse($this->conditions);
        if (!empty($yaml['conditions']) && is_array($yaml['conditions'])) {
            foreach ($yaml['conditions'] as $yamlCondition) {
                $condition = [];
                $condition['attributeName'] = '';
                $condition['attributeValue'] = '';
                $condition['operation'] = '';

                foreach ($yamlCondition as $key => $val) {
                    if ($key === 'operation') {
                        $condition['operation'] = $val;
                    } else {
                        $condition['attributeName'] = $key;
                        $condition['attributeValue'] = $val;
                    }
                }

                $conditions[] = $condition;
            }
        }
        return $conditions;
    }
}
