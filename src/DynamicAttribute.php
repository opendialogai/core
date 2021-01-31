<?php

namespace OpenDialogAi\Core;

use Illuminate\Database\Eloquent\Model;

class DynamicAttribute extends Model
{
    public $timestamps = false;

    protected $table = 'dynamic_attributes';
    protected $fillable = ['attribute_id', 'attribute_type'];

}
