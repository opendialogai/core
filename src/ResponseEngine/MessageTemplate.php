<?php

namespace OpenDialogAi\ResponseEngine;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use Spatie\Activitylog\Traits\LogsActivity;
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
 * @property array history
 * @method static Builder forIntent($intentName) Local scope for messages for intent name
 */
class MessageTemplate extends Model
{
    use LogsActivity;

    const CONDITION = 'condition';
    const CONDITIONS = 'conditions';
    const ATTRIBUTES = 'attributes';
    const PARAMETERS = 'parameters';
    const OPERATION  = 'operation';

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
        'version_number',
        'history',
    ];

    // Create activity logs when the conditions or message markup attribute is updated.
    protected static $logAttributes = ['conditions', 'message_markup', 'version_number'];

    protected static $logName = 'message_template_log';

    protected static $submitEmptyLogs = false;

    // Don't create activity logs when these model attributes are updated.
    protected static $ignoreChangedAttributes = [
        'updated_at',
        'yaml_validation_status',
        'yaml_schema_validation_status',
        'scenes_validation_status',
        'model_validation_status'
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
                    $condition[self::ATTRIBUTES] = '';
                    $condition[self::PARAMETERS] = '';
                    $condition[self::OPERATION] = '';

                    foreach ($yamlCondition[self::CONDITION] as $key => $val) {
                        switch ($key) {
                            case ResponseEngineServiceInterface::ATTRIBUTES_KEY:
                                $condition[self::ATTRIBUTES] = $val;
                                break;
                            case ResponseEngineServiceInterface::OPERATION_KEY:
                                $condition[self::OPERATION] = $val;
                                break;
                            case ResponseEngineServiceInterface::PARAMETERS_KEY:
                                $condition[self::PARAMETERS] = $val;
                                break;
                            default:
                                break;
                        }
                    }

                    $operation = $condition[self::OPERATION];
                    $attributes = $condition[self::ATTRIBUTES];
                    $parameters = $condition[self::PARAMETERS];
                    $conditions[] = new Condition($operation, $attributes, $parameters);
                }
            }
        }

        return $conditions;
    }

    /**
     * @return array
     */
    public function getHistoryAttribute(): array
    {
        $history = MessageTemplateActivity::forSubjectOrdered($this->id)->get();

        return $history->filter(function ($item) {
            // Retain if it's the first activity record or if it's a record with the version has incremented
            return isset($item['properties']['old'])
                && isset($item['properties']['old']['version_number'])
                && isset($item['properties']['attributes']['version_number'])
                && $item['properties']['attributes']['version_number'] != $item['properties']['old']['version_number'];
        })->values()->map(function ($item) {
            return [
                'id' => $item['id'],
                'timestamp' => $item['updated_at'],
                'attributes' => $item['properties']['attributes']
            ];
        })->toArray();
    }

    /**
     * $id
     * @return \OpenDialogAi\ResponseEngine\MessageTemplate
     */
    public static function messageTemplateWithHistory($id): MessageTemplate
    {
        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = self::find($id);
        if (!is_null($messageTemplate)) {
            $messageTemplate->setAppends(array_merge($messageTemplate->appends, ['history']));
        }

        return $messageTemplate;
    }
}
