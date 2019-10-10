<?php

namespace OpenDialogAi\ConversationLog;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Webchat\Message
 *
 * @property int $id
 * @property string $user_id
 * @property string $author
 * @property string $message
 * @property string $message_id
 * @property string $type
 * @property string $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $user
 * @property int microtime
 */
class Message extends Model
{
    /** @var array */
    protected $fillable = [
        'user_id',
        'author',
        'message',
        'message_id',
        'type',
        'data',
        'microtime',
        'user',
        'intent',
        'conversation',
        'scene'
    ];

    /**
     * Deserialize the data field
     *
     * @param $value
     * @return mixed|null
     */
    public function getDataAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return unserialize($value);
    }

    /**
     * Deserialize the user field
     *
     * @param $value
     * @return mixed|null
     */
    public function getUserAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return unserialize($value);
    }

    public function chatbotUser()
    {
        return $this->belongsTo(ChatbotUser::class, 'user_id');
    }

    public function scopeBefore(Builder $query, $date): Builder
    {
        return $query->where('microtime', '<=', Carbon::parse($date));
    }

    public function scopeAfter(Builder $query, $date): Builder
    {
        return $query->where('microtime', '>', Carbon::parse($date));
    }

    public static function create(
        $microtime,
        $type,
        $userId,
        $author,
        $message,
        $data = null,
        $messageId = null,
        $user = null,
        $intent = null,
        $conversation = null,
        $scene = null
    ) {
        // Generate a message ID if we weren't given one.
        if (empty($messageId)) {
            $messageId = (string) Str::uuid();
        }

        // Generate a timestamp if we weren't given one.
        if (empty($microtime)) {
            $microtime = DateTime::createFromFormat('U.u', microtime(true))->format('Y-m-d H:i:s.u');
        }

        $message = new self([
            'microtime'       => $microtime,
            'type'            => $type,
            'user_id'         => $userId,
            'author'          => $author,
            'message'         => $message,
            'data'            => $data ? serialize($data) : null,
            'message_id'      => $messageId,
            'user'            => $user ? serialize($user) : null,
            'intent'          => $intent,
            'conversation'    => $conversation,
            'scene'           => $scene
        ]);

        return $message;
    }

    public function happenedLessThan(int $seconds)
    {
        $lastValidTime = Carbon::createFromTimeString($this->microtime)->addSeconds($seconds);

        if ($lastValidTime->greaterThan(new Carbon())) {
            return true;
        }
        return false;
    }
}
