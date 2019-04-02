<?php

namespace Utterances;

use OpenDialogAi\Core\Utterances\BaseUtterance;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

/**
 * Chat open utterances do not include text
 */
abstract class ChatOpenUtterance extends BaseUtterance
{
    const TYPE = 'chat_open';

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        throw new FieldNotSupported('Text field is not supported by chat open utterances');
    }

    /**
     * @inheritdoc
     */
    public function setText($text) :void
    {
        throw new FieldNotSupported('Text field is not supported by chat open utterances');
    }
}
