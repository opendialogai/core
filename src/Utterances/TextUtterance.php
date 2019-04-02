<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

/**
 * Text utterances do not support callback ids or users
 */
abstract class TextUtterance extends BaseUtterance
{
    const TYPE = 'text';

    /**
     * @inheritdoc
     */
    public function getCallbackId(): string
    {
        throw new FieldNotSupported('Callback ID field is not supported by text utterances');
    }

    /**
     * @inheritdoc
     */
    public function setCallbackId(string $callbackId) :void
    {
        throw new FieldNotSupported('Callback ID field is not supported by text utterances');
    }

    /**
     * @inheritdoc
     */
    public function getUser(): User
    {
        throw new FieldNotSupported('User field is not supported by text utterances');
    }

    /**
     * @inheritdoc
     */
    public function setUser(User $user) :void
    {
        throw new FieldNotSupported('User field is not supported by text utterances');
    }
}
