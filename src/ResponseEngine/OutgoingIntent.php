<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property String $name
 */
class OutgoingIntent extends Model
{
    protected $fillable = ['name'];

    protected $visible = [
        'name',
        'messageTemplates',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the message templates for the outgoing intent.
     */
    public function messageTemplates()
    {
        return $this->hasMany('OpenDialogAi\ResponseEngine\MessageTemplate');
    }
}
