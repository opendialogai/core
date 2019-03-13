<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    /**
     * Get the outgoing intent that owns the message template.
     */
    public function outgoingIntent()
    {
        return $this->belongsTo('ResponseEngine\OutgoingIntent');
    }

    /**
     * Helper method: return an array of conditions.
     */
    public function getConditions()
    {
        // @TODO
        return [];
    }
}
