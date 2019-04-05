<?php

namespace OpenDialogAi\ConversationBuilder;

use Illuminate\Database\Eloquent\Model;

class ConversationLog extends Model
{
    protected $fillable = [
        'conversation_id',
        'name',
        'message',
        'type',
    ];

    /**
     * Get the Conversation that owns the Log.
     */
    public function conversation()
    {
        return $this->belongsTo('OpenDialogAi\ConversationBulider\Conversation');
    }
}
