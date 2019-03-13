<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutgoingIntent extends Model
{
    /**
     * Get the message templates for the outgoing intent.
     */
    public function messageTemplates()
    {
        return $this->hasMany('ResponseEngine\MessageTemplate');
    }
}
