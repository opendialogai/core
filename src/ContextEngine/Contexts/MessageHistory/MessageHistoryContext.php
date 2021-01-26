<?php

namespace OpenDialogAi\ContextEngine\Contexts\MessageHistory;

use OpenDialogAi\AttributeEngine\AttributeInterface;
use OpenDialogAi\AttributeEngine\StringAttribute;
use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Utterances\FormResponseUtterance;
use OpenDialogAi\Core\Utterances\TriggerUtterance;
use OpenDialogAi\ResponseEngine\Message\HandToSystemMessage;

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
            ->orderBy('microtime', 'asc')
            ->whereNotIn('type', ['chat_open'])
            ->get();

        foreach ($messages as $message) {
            $messageText = $message->message;

            if ($messageText == '' && isset($message->data['text'])) {
                $messageText = $message->data['text'];
            } elseif ($message->type == FormResponseUtterance::TYPE) {
                $messageText = 'Form submitted.';
            } elseif ($message->type == TriggerUtterance::TYPE) {
                $messageText = '(Trigger message)';
            } elseif ($message->type == HandToSystemMessage::TYPE) {
                $messageText = '(User was handed over to another system)';
            }

            $author = $message->author == "them" ? "Bot" : "User";
            $messageHistory[] = sprintf('%s: %s<br/>', $author, $messageText);
        }

        $messageHistory = urlencode(implode("\n", $messageHistory));

        return new StringAttribute('all', $messageHistory);
    }
}
