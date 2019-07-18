<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Model;

class LinkClick  extends Model
{
    protected $fillable = ['user_id', 'url', 'date'];

    public $timestamps = false;
}
