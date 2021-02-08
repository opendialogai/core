<?php

namespace OpenDialogAi\AttributeEngine;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $attribute_id
 * @property string $attribute_type
 */
class DynamicAttribute extends Model
{
    protected $table = 'dynamic_attributes';

    protected $fillable = ['attribute_id', 'attribute_type'];
}
