<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

abstract class FormResponseUtterance extends ButtonResponseUtterance
{
    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        throw new FieldNotSupported('Value field is not supported by button response utterances');
    }

    /**
     * @inheritDoc
     */
    public function setValue(string $value): void
    {
        throw new FieldNotSupported('Value field is not supported by button response utterances');
    }
}
