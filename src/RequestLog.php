<?php

namespace OpenDialogAi\Core;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function responseLog()
    {
        return $this->belongsTo(ResponseLog::class, 'request_id', 'request_id');
    }
}
