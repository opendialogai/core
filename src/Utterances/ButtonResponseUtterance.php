<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

/**
 * Button response utterances do not include text
 */
abstract class ButtonResponseUtterance extends BaseUtterance
{
    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        throw new FieldNotSupported('Text field is not supported by button response utterances');
    }

    /**
     * @inheritdoc
     */
    public function setText(string $text): void
    {
        throw new FieldNotSupported('Text field is not supported by button response utterances');
    }
}
