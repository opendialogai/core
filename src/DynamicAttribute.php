<?php

namespace OpenDialogAi\Core;

use Illuminate\Database\Eloquent\Model;

class DynamicAttribute extends Model
{
    protected $table = 'dynamic_attributes';
    protected $fillable = ['attribute_id', 'attribute_type'];

}
