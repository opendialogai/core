<?php

namespace OpenDialogAi\Core\Utterances;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

/**
 * An utterance defines
 */
interface UtteranceInterface
{
    const WEBCHAT = 'webchat';

    /**
     * Gets the user id of the person sending the message
     *
     * @return string
     * @throws FieldNotSupported
     */
    public function getUserId() : string;

    /**
     * @param string $userId
     * @throws FieldNotSupported
     */
    public function setUserId(string $userId): void;

    /**
     * Gets the text of the message
     *
     * @return string
     * @throws FieldNotSupported
     */
    public function getText() : string;

    /**
     * @param string $text
     * @throws FieldNotSupported
     */
    public function setText(string $text): void;

    /**
     * @return User
     * @throws FieldNotSupported
     */
    public function getUser(): User;

    /**
     * @param User $user
     * @throws FieldNotSupported
     */
    public function setUser(User $user): void;

    /**
     * @return string
     * @throws FieldNotSupported
     */
    public function getMessageId(): string;

    /**
     * @param string $messageId
     * @throws FieldNotSupported
     */
    public function setMessageId(string $messageId): void;

    /**
     * @return float
     * @throws FieldNotSupported
     */
    public function getTimestamp(): float;

    /**
     * @param float $timestamp
     * @throws FieldNotSupported
     */
    public function setTimestamp(float $timestamp): void;

    /**
     * Returns the platform that this utterance originated from
     *
     * @return string
     * @throws FieldNotSupported
     */
    public function getPlatform() : string;

    /**
     * Returns the type of utterance
     *
     * @return string
     * @throws FieldNotSupported
     */
    public function getType() : string;

    /**
     * @return string
     * @throws FieldNotSupported
     */
    public function getCallbackId(): string;

    /**
     * @param string $callbackId
     * @throws FieldNotSupported
     */
    public function setCallbackId(string $callbackId): void;
}