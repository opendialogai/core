<?php

namespace OpenDialogAi\Core;

use Illuminate\Database\Eloquent\Model;

class UserAttributes extends Model
{
    protected $fillable = ['user_id', 'attribute', 'value'];
}
