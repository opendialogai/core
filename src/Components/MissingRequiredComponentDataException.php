<?php


namespace OpenDialogAi\Core\Components;

use Exception;

class MissingRequiredComponentDataException extends Exception
{
    public string $data;

    /**
     * MissingRequiredComponentDataException constructor.
     * @param string $data
     */
    public function __construct(string $data)
    {
        parent::__construct();

        $this->data = $data;
    }
}
