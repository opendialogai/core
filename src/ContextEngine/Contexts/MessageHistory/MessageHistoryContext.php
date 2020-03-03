<?php

namespace OpenDialogAi\ContextEngine\Contexts\MessageHistory;

use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;

class MessageHistoryContext extends AbstractContext
{
    public const MESSAGE_HISTORY_CONTEXT = 'message_history';

    public function __construct()
    {
        parent::__construct(self::MESSAGE_HISTORY_CONTEXT);
    }

    public function getAttribute(string $attributeName): AttributeInterface
    {
        switch ($attributeName) {
            case 'all':
                return $this->getAllMessageHistoryAttribute();
                break;
        }

        return parent::getAttribute($attributeName);
    }

    /**
     * @return StringAttribute
     */
    private function getAllMessageHistoryAttribute()
    {
        $messageHistory = [];

        $userId = ContextService::getUserContext()->getUserId();

        $messages = Message::where('user_id', $userId)
            ->orderBy('microtime', 'desc')
            ->whereNotIn('type', ['chat_open'])
            ->get();

        foreach ($messages as $message) {
            $messageText = $message->message;

            if ($messageText == '' && isset($message->data['text'])) {
                $messageText = $message->data['text'];
            }

            $messageHistory[] = sprintf('%s: %s - %s', $message->author, $messageText, $message->created_at);
        }

        $messageHistory = implode("\n", $messageHistory);

        return new StringAttribute('all', $messageHistory);
    }
}
