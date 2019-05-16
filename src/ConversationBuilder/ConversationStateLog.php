<?php

namespace OpenDialogAi\ConversationBuilder;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int conversation_id
 * @property string type
 * @property string message
 */
class ConversationStateLog extends Model
{
    protected $fillable = [
        'conversation_id',
        'message',
        'type',
    ];

    /**
     * Get the Conversation that owns the State Log.
     */
    public function conversation()
    {
        return $this->belongsTo('OpenDialogAi\ConversationBuilder\Conversation');
    }
}
