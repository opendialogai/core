<?php

namespace OpenDialogAi\ConversationLog\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use OpenDialogAi\ConversationLog\Message;

class WebchatInitController extends BaseController
{
    public function receive($user_id, $limit = 10)
    {
        $messages = Message::where('user_id', $user_id)
            ->orderBy('microtime', 'desc')
            ->limit($limit)
            ->where('type', '<>', 'chat_open')
            ->get();

        return $messages;
    }
}
