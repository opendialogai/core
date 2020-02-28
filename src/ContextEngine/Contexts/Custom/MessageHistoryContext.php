<?php

namespace OpenDialogAi\ContextEngine\Contexts\Custom;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;

class MessageHistoryContext extends AbstractCustomContext
{
    public static $name = 'message_history';

    public function loadAttributes(): void
    {
        //
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
