<?php

namespace OpenDialogAi\ConversationLog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use OpenDialogAi\ConversationLog\Message;

class WebchatInitController extends BaseController
{
    public function receive(Request $request, $user_id, $limit = 10)
    {
        $ignoreTypes = array_filter(explode(',', $request->query('ignore', '')));

        $messages = Message::where('user_id', $user_id)
            ->orderBy('microtime', 'desc')
            ->limit($limit)
            ->whereNotIn('type', $ignoreTypes)
            ->get();

        return $messages;
    }
}
