<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

/**
 * Url click utterances do not include text
 */
abstract class UrlClickUtterance extends BaseUtterance
{
    const TYPE = 'url_click';

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        throw new FieldNotSupported('Text field is not supported by url click utterances');
    }

    /**
     * @inheritdoc
     */
    public function setText(string $text) :void
    {
        throw new FieldNotSupported('Text field is not supported by url click utterances');
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        throw new FieldNotSupported('Value field is not supported by url click utterances');
    }

    /**
     * @inheritdoc
     */
    public function setValue(string $text) :void
    {
        throw new FieldNotSupported('Value field is not supported by url click utterances');
    }
}
