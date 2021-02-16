<?php

namespace OpenDialogAi\ContextEngine\Contexts\MessageHistory;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\BaseContext;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\ResponseEngine\Message\HandToSystemMessage;

class MessageHistoryContext extends AbstractContext
{
    public const MESSAGE_HISTORY_CONTEXT = 'message_history';
    protected static ?string $componentId = self::MESSAGE_HISTORY_CONTEXT;

    protected static bool $attributesAreReadOnly = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function getAttribute(string $attributeName): Attribute
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

        //$userId = ContextService::getUserContext()->getUserId();

        $messages = Message::where('user_id', $userId)
            ->orderBy('microtime', 'asc')
            ->whereNotIn('type', ['chat_open'])
            ->get();

        foreach ($messages as $message) {
            $messageText = $message->message;

            if ($messageText == '' && isset($message->data['text'])) {
                $messageText = $message->data['text'];
            } elseif ($message->type == 'form_response') {
                $messageText = 'Form submitted.';
            } elseif ($message->type == 'trigger') {
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
