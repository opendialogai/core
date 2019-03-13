<?php

namespace OpenDialogAi\ActionEngine;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property String $name
 */
class Action extends Model
{
    protected $fillable = ['name'];
}
