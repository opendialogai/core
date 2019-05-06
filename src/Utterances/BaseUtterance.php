<?php

namespace OpenDialogAi\Core\Utterances;

use OpenDialogAi\Core\Utterances\Exceptions\UtterancePlatformNotSetException;
use OpenDialogAi\Core\Utterances\Exceptions\UtteranceTypeNotSetException;

/**
 * The base utterance that all utterances should extend. All sub classes need to define their own TYPE and PLATFORM
 * constants to be valid
 */
abstract class BaseUtterance implements UtteranceInterface
{
    const TYPE = 'base';

    const PLATFORM = 'core';

    /** @var string The id of the user sending the message */
    protected $userId = null;

    /** @var string The text of the message being sent */
    protected $text = null;

    /** @var User More granular user information */
    protected $user = null;

    /** @var string The id of the individual message */
    protected $messageId = '';

    /** @var float The time the message was received by OpenDialog */
    protected $timestamp = null;

    /** @var string The callback id associated with the utterance */
    protected $callbackId;

    /** @var string */
    protected $value;

    /** @var array */
    protected $data = [];

    public function __construct()
    {
        $this->timestamp = microtime(true);
    }

    /**
     * Returns the utterance platform. Classes that extend this class can set their own type by defining a type constant
     *
     * @return string
     * @throws UtterancePlatformNotSetException
     */
    public function getPlatform(): string
    {
        return static::PLATFORM;
    }

    /**
     * Returns the type of utterance. Classes that extend this class can set their own type by defining a type constant
     *
     * @return string
     * @throws UtteranceTypeNotSetException
     */
    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @inheritdoc
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @inheritdoc
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @inheritdoc
     */
    public function setMessageId(string $messageId): void
    {
        $this->messageId = $messageId;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * @inheritdoc
     */
    public function setTimestamp(float $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @inheritdoc
     */
    public function getCallbackId(): string
    {
        if (!isset($this->callbackId)) {
            return '';
        }
        return $this->callbackId;
    }

    /**
     * @inheritdoc
     */
    public function setCallbackId(string $callbackId): void
    {
        $this->callbackId = $callbackId;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
