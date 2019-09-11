<?php

namespace OpenDialogAi\ResponseEngine;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\Condition\Condition;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
    const ATTRIBUTE_NAME  = 'attribute';
    const ATTRIBUTE_VALUE = 'value';
    const OPERATION       = 'operation';

    protected $fillable = [
        'name',
        'conditions',
        'message_markup',
        'outgoing_intent_id',
    ];

    protected $visible = [
        'name',
        'conditions',
        'message_markup',
        'outgoing_intent_id',
        'created_at',
        'updated_at',
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
     * Counts the number of conditions on the message template
     *
     * @return int
     */
    public function getNumberOfConditions(): int
    {
        if (isset($this->conditions)) {
            $yaml = Yaml::parse($this->conditions);
            if (!empty($yaml[self::CONDITIONS]) && is_array($yaml[self::CONDITIONS])) {
                return count($yaml[self::CONDITIONS]);
            }
        }

        return 0;
    }

    /**
     * Helper method: return an array of conditions
     *
     * @return array|Condition
     */
    public function getConditions()
    {
        $conditions = [];

        if (isset($this->conditions)) {
            $yaml = Yaml::parse($this->conditions);
            if (!empty($yaml[self::CONDITIONS]) && is_array($yaml[self::CONDITIONS])) {
                foreach ($yaml[self::CONDITIONS] as $yamlCondition) {
                    $condition = [];
                    $condition[self::ATTRIBUTE_NAME] = '';
                    $condition[self::ATTRIBUTE_VALUE] = '';
                    $condition[self::OPERATION] = '';

                    foreach ($yamlCondition['condition'] as $key => $val) {
                        switch ($key) {
                            case ResponseEngineServiceInterface::ATTRIBUTE_NAME_KEY:
                                $condition[self::ATTRIBUTE_NAME] = $val;
                                break;
                            case ResponseEngineServiceInterface::ATTRIBUTE_OPERATION_KEY:
                                $condition[self::OPERATION] = $val;
                                break;
                            case ResponseEngineServiceInterface::ATTRIBUTE_VALUE_KEY:
                                $condition[self::ATTRIBUTE_VALUE] = $val;
                                break;
                            default:
                                break;
                        }
                    }

                    [$contextId, $attributeName] = ContextParser::determineContextAndAttributeId($condition['attribute']);

                    $attribute = AttributeResolver::getAttributeFor($attributeName, $condition['value']);

                    $conditions[$contextId][] = [$attributeName => new Condition($attribute, $condition['operation'])];
                }
            }
        }

        return $conditions;
    }
}
