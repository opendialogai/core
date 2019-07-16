<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

abstract class FormResponseUtterance extends BaseUtterance
{
    const TYPE = 'form_response';

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        throw new FieldNotSupported('Text field is not supported by form response utterances');
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        throw new FieldNotSupported('Value field is not supported by form response utterances');
    }

    /**
     * @inheritdoc
     */
    public function setText(string $text): void
    {
        throw new FieldNotSupported('Text field is not supported by form response utterances');
    }

    /**
     * @inheritDoc
     */
    public function setValue(string $value): void
    {
        throw new FieldNotSupported('Value field is not supported by form response utterances');
    }
}
