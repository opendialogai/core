<?php

namespace OpenDialogAi\MessageBuilder\Message;

class DatePickerMessage
{
    public $callback;

    public $submitText;

    public $text;

    public $maxDate;

    public $minDate;

    public $dayRequired;

    public $monthRequired;

    public $yearRequired;

    public $attributeName;

    /**
     * DatePickerMessage constructor.
     * @param string $text
     * @param string $callback
     * @param string $submitText
     * @param string $attributeName
     * @param string $maxDate 'today' or 'yyyymmdd'
     * @param string $minDate 'today' or 'yyyymmdd'
     * @param bool $dayRequired
     * @param bool $monthRequired
     * @param bool $yearRequired
     */
    public function __construct(
        $text,
        $callback,
        $submitText,
        $attributeName,
        $maxDate = null,
        $minDate = null,
        $dayRequired = true,
        $monthRequired = true,
        $yearRequired = true
    ) {
        $this->text = $text;
        $this->callback = $callback;
        $this->submitText = $submitText;
        $this->attributeName = $attributeName;
        $this->maxDate = $maxDate;
        $this->minDate = $minDate;
        $this->dayRequired = $dayRequired;
        $this->monthRequired = $monthRequired;
        $this->yearRequired = $yearRequired;
    }

    public function getMarkUp()
    {
        return <<<EOT
<date-picker-message>
    <text>$this->text</text>
    <callback>$this->callback</callback>
    <submit_text>$this->submitText</submit_text>
    <day_required>$this->dayRequired</day_required>
    <month_required>$this->monthRequired</month_required>
    <year_required>$this->yearRequired</year_required>
    <max_date>$this->maxDate</max_date>
    <min_date>$this->minDate</min_date>
    <attribute_name>$this->attributeName</attribute_name>
</date-picker-message>
EOT;
    }
}
