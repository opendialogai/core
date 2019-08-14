<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

/**
 * Longtext response utterances do not include text
 */
abstract class LongtextResponseUtterance extends BaseUtterance
{
    const TYPE = 'longtext_response';

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        throw new FieldNotSupported('Text field is not supported by longtext response utterances');
    }

    /**
     * @inheritdoc
     */
    public function setText(string $text): void
    {
        throw new FieldNotSupported('Text field is not supported by longtext response utterances');
    }
}
