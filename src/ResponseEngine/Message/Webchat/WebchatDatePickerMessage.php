<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\DatePickerMessage;

class WebchatDatePickerMessage extends WebchatMessage implements DatePickerMessage
{
    protected $messageType = self::TYPE;

    private $callback;

    private $submitText;

    private $maxDate;

    private $minDate;

    private $dayRequired;

    private $monthRequired;

    private $yearRequired;

    private $attributeName;

    /**
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     * @return WebchatDatePickerMessage
     */
    public function setMessageType(string $messageType): WebchatDatePickerMessage
    {
        $this->messageType = $messageType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }

    /**
     * @param mixed $maxDate
     * @return WebchatDatePickerMessage
     */
    public function setMaxDate($maxDate)
    {
        $this->maxDate = $maxDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * @param mixed $minDate
     * @return WebchatDatePickerMessage
     */
    public function setMinDate($minDate)
    {
        $this->minDate = $minDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDayRequired()
    {
        return $this->dayRequired;
    }

    /**
     * @param mixed $dayRequired
     * @return WebchatDatePickerMessage
     */
    public function setDayRequired($dayRequired)
    {
        $this->dayRequired = $dayRequired;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthRequired()
    {
        return $this->monthRequired;
    }

    /**
     * @param mixed $monthRequired
     * @return WebchatDatePickerMessage
     */
    public function setMonthRequired($monthRequired)
    {
        $this->monthRequired = $monthRequired;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYearRequired()
    {
        return $this->yearRequired;
    }

    /**
     * @param mixed $yearRequired
     * @return WebchatDatePickerMessage
     */
    public function setYearRequired($yearRequired)
    {
        $this->yearRequired = $yearRequired;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param mixed $callback
     * @return WebchatDatePickerMessage
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmitText()
    {
        return $this->submitText;
    }

    /**
     * @param mixed $submitText
     * @return WebchatDatePickerMessage
     */
    public function setSubmitText($submitText)
    {
        $this->submitText = $submitText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @param mixed $attributeName
     * @return WebchatDatePickerMessage
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return parent::getData() + [
                'day_required' => $this->getDayRequired(),
                'month_required' => $this->getMonthRequired(),
                'year_required' => $this->getYearRequired(),
                'max_date' => $this->getMaxDate(),
                'min_date' => $this->getMinDate(),
                'callback' => $this->getCallback(),
                'submit_text' => $this->getSubmitText(),
                'attribute_name' => $this->getAttributeName()
            ];
    }
}
