<?php

namespace OpenDialogAi\ResponseEngine\Message;

/**
 * Date Picker Message. Has 3 fields - 1 each for day, month, year.
 * Also can optionally have a min and max date set
 */
interface DatePickerMessage extends OpenDialogMessage
{
    const TYPE = 'date-picker';
    
    public function getMaxDate();
    
    public function setMaxDate(string $date);
    
    public function getMinDate();
    
    public function setMinDate(string $data);
    
    public function getDayRequired();
    
    public function setDayRequired(bool $dayRequired);

    public function getMonthRequired();

    public function setMonthRequired(bool $monthRequired);

    public function getYearRequired();

    public function setYearRequired(bool $yearRequired);
}
