<?php

namespace OpenDialogAi\Core\ResponseEngine\Message\Webchat;

use OpenDialogAi\Core\Contracts\OpenDialogMessageContract;

class WebchatMessage implements OpenDialogMessageContract
{
    const TIME = 'time';

    const DATE = 'date';

    protected $messageType = 'text';

    /** The message text. */
    private $text = null;

    private $disable_text = false;

    private $time;

    private $date;

    private $isEmpty = false;

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
        if ($noSpecialChars) {
            $this->text = vsprintf($format, $args);
        } else {
            // Escape &, <, > characters
            $this->text = vsprintf(htmlspecialchars($format, ENT_NOQUOTES), $args);
        }
        return $this;
    }

    /**
     * @return null|string
     */
    public function getText():?string
    {
        return $this->text;
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
     * @return array
     */
    public function getData():?array
    {
        return [
            'text' => $this->getText(),
            self::TIME => $this->getTime(),
            self::DATE => $this->getDate()
        ];
    }

    public function getMessageToPost()
    {
        if ($this->isEmpty) {
            return false;
        }
        return [
            'author' => 'them',
            'type' => $this->getMessageType(),
            'data' => $this->getData()
        ];
    }
}