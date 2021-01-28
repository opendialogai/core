<?php

namespace OpenDialogAi\Core;

use App\Http\Resources\DynamicAttributeCollection;
use Illuminate\Database\Eloquent\Model;

class DynamicAttribute extends Model
{
    static public $validIdPattern = "/^([a-z]+_)*[a-z]+$/";
    static public $validTypePattern = "/^attribute\.[A-Za-z]*\.[A-Za-z_]*$/";

    public $timestamps = false;

    protected $table = 'dynamic_attributes';
    protected $fillable = ['attribute_id', 'attribute_type'];

    /**
     * Checks if the id of the DynamicAttribute is in the right format
     *
     * @param string $id
     * @return bool
     */
    public static function isValidId(string $id) : bool
    {
        return preg_match(static::$validIdPattern, $id) === 1;
    }

    /**
     * Checks if the type of the DynamicAttribute is in the right format
     *
     * @param string $type
     * @return bool
     */
    public static function isValidType(string $type) : bool
    {
        return preg_match(static::$validTypePattern, $type) === 1;
    }

}
