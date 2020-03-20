<?php

namespace OpenDialogAi\ContextEngine\Contexts\MessageHistory;

use OpenDialogAi\ContextEngine\ContextManager\AbstractContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Utterances\FormResponseUtterance;
use OpenDialogAi\Core\Utterances\TriggerUtterance;
use OpenDialogAi\ResponseEngine\Message\HandToHumanMessage;

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
            } else if ($message->type == FormResponseUtterance::TYPE) {
                $messageText = 'Form submitted.';
            } else if ($message->type == TriggerUtterance::TYPE) {
                $messageText = '(Trigger message)';
            } else if ($message->type == HandToHumanMessage::TYPE) {
                $messageText = '(User speaking to human)';
            }

            $author = $message->author == "them" ? "Bot" : "User";
            $messageHistory[] = sprintf('%s: %s<br/>', $author, $messageText);
        }

        $messageHistory = urlencode(implode("\n", $messageHistory));

        return new StringAttribute('all', $messageHistory);
    }
}
