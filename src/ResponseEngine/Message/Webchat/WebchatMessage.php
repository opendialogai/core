<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;

abstract class WebchatMessage implements OpenDialogMessage
{
    protected $messageType = 'base';

    const TIME = 'time';

    const DATE = 'date';

    /** The message text. */
    private $text = null;

    private $disable_text = false;

    private $time;

    private $date;

    private $hidetime = false;

    private $hideAvatar = false;

    private $internal = false;

    private $isEmpty = false;

    private $intent;

    public function __construct()
    {
        $this->time = date('h:i A');
        $this->date = date('D j M');
    }

    /**
     * Sets text for a standard Web Chat message. The main text is escaped
     *
     * @param $format - main message text
     * @param array $args - replaced in format
     * @param bool $noSpecialChars
     * @return $this
     */
    public function setText($format, $args = [], bool $noSpecialChars = false)
    {
        if (is_null($format) || $format == "") {
            $this->text = null;
        } elseif ($noSpecialChars) {
            $this->text = vsprintf($format, $args);
        } else {
            // Escape &, <, > characters
            $this->text = vsprintf(htmlspecialchars($format, ENT_NOQUOTES), $args);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Set disable_text property
     *
     * @param $disable_text
     * @return $this
     */
    public function setDisableText($disable_text)
    {
        $this->disable_text = $disable_text;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDisableText()
    {
        return $this->disable_text;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function getInternal()
    {
        return $this->internal;
    }

    /**
     * Set internal property
     *
     * @param $internal
     * @return $this
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHidetime()
    {
        return $this->hidetime;
    }

    /**
     * Set hidetime property
     *
     * @param $hidetime
     * @return $this
     */
    public function setHidetime($hidetime)
    {
        $this->hidetime = $hidetime;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHideAvatar()
    {
        return $this->hideAvatar;
    }

    /**
     * Set hideAvatar property
     *
     * @param $hideAvatar
     * @return $this
     */
    public function setHideAvatar($hideAvatar)
    {
        $this->hideAvatar = $hideAvatar;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * Set intent property
     *
     * @param $intent
     * @return $this
     */
    public function setIntent(string $intent): OpenDialogMessage
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function setAsEmpty(): void
    {
        $this->isEmpty = true;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return [
            'text' => $this->getText(),
            'disable_text' => $this->getDisableText(),
            'internal' => $this->getInternal(),
            'hidetime' => $this->getHidetime(),
            'hideavatar' => $this->getHideAvatar(),
            self::TIME => $this->getTime(),
            self::DATE => $this->getDate()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageToPost()
    {
        if ($this->isEmpty) {
            return false;
        }
        return [
            'author' => 'them',
            'type' => $this->getMessageType(),
            'intent' => $this->getIntent(),
            'data' => $this->getData()
        ];
    }
}
