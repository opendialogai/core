<?php

namespace OpenDialogAi\Core;

use Illuminate\Support\ServiceProvider;

class LoggingHelper extends ServiceProvider
{
    /**
     * Gets a log processor that will add a unique request id to every log message along with a user id if one is found
     * in the request
     *
     * @return \Closure
     */
    public static function getLogUserIdProcessor($requestId = null): \Closure
    {
        if ($requestId === null) {
            $requestId = uniqid();
        }

        $userId = self::getUserIdFromRequest();

        return function ($record) use ($requestId, $userId) {
            $record['extra']['request_id'] = $requestId;

            if ($userId) {
                $record['extra']['user_id'] = $userId;
            }

            return $record;
        };
    }

    /**
     * Tries to get a user id from the request
     *
     * @return null
     */
    public static function getUserIdFromRequest()
    {
        $userId = null;
        if ($content = request()->getContent()) {
            $incoming = json_decode($content);
            if (isset($incoming->user_id)) {
                $userId = $incoming->user_id;
            }

            if (isset($incoming->data->user_id)) {
                $userId = $incoming->user_id;
            }
        }

        return $userId;
    }
}
