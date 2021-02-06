<?php


namespace OpenDialogAi\Core\Components;

use Exception;

class InvalidComponentDataException extends Exception
{
    public string $data;
    public string $value;

    /**
     * InvalidComponentDataException constructor.
     * @param string $data
     * @param string $value
     */
    public function __construct(string $data, string $value)
    {
        parent::__construct();

        $this->data = $data;
        $this->value = $value;
    }
}
