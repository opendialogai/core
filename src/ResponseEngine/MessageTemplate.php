<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenDialogAi\Core\Attribute\Condition;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
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
 * @method static Builder forIntent($intentName) Local scope for messages for intent name
 */
class MessageTemplate extends Model
{
    const CONDITIONS      = 'conditions';
    const ATTRIBUTE_NAME  = 'attributeName';
    const ATTRIBUTE_VALUE = 'attributeValue';
    const OPERATION       = 'operation';

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
     * @param Builder $query
     * @param string $intentName
     * @return Builder
     */
    public function scopeForIntent($query, $intentName)
    {
        return $query
            ->join('outgoing_intents', 'outgoing_intents.id', '=', 'message_templates.outgoing_intent_id')
            ->where('outgoing_intents.name', $intentName);
    }

    /**
     * Helper method: return an array of conditions
     *
     * TODO - can this return an array of @see Condition
     *
     * @return array
     */
    public function getConditions()
    {
        $conditions = [];

        $yaml = Yaml::parse($this->conditions);
        if (!empty($yaml[self::CONDITIONS]) && is_array($yaml[self::CONDITIONS])) {
            foreach ($yaml[self::CONDITIONS] as $yamlCondition) {
                $condition = [];
                $condition[self::ATTRIBUTE_NAME] = '';
                $condition[self::ATTRIBUTE_VALUE] = '';
                $condition[self::OPERATION] = '';

                foreach ($yamlCondition as $key => $val) {
                    if ($key === ResponseEngineServiceInterface::ATTRIBUTE_OPERATION_KEY) {
                        $condition[self::OPERATION] = $val;
                    } else {
                        $condition[self::ATTRIBUTE_NAME] = $key;
                        $condition[self::ATTRIBUTE_VALUE] = $val;
                    }
                }

                $conditions[] = $condition;
            }
        }
        return $conditions;
    }
}
