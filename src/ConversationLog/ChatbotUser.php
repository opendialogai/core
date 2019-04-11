<?php

namespace OpenDialogAi\ConversationLog;

use Illuminate\Database\Eloquent\Model;

/**
 * OpenDialogAi\ConversationLog\ChatbotUser
 *
 * @property string $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon $hand_to_human
 * @property string $email
 * @property $registered
 */
class ChatbotUser extends Model
{
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';

    /** @var array */
    protected $fillable = [
        'user_id',
        'ip_address',
        'country',
        'browser_language',
        'os',
        'browser',
        'timezone',
        'first_name',
        'last_name',
        'email',
        'platform',
    ];

    protected $appends = ['registered'];

    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id', 'user_id')
            ->orderBy('microtime', 'desc');
    }

    /**
     * Gets only registered users - ie where they have an email address
     *
     * @param $query
     * @return mixed
     */
    public function scopeRegistered($query)
    {
        return $query->where('email', '<>', null);
    }

    /**
     * Gets only unregistered users - ie where they do not have an email address
     *
     * @param $query
     * @return mixed
     */
    public function scopeUnregistered($query)
    {
        return $query->where('email', null);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'user_id';
    }

    public function getRegisteredAttribute()
    {
        return $this->email == null;
    }
}
