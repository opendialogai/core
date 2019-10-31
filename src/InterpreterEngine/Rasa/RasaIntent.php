<?php

namespace OpenDialogAi\InterpreterEngine\Rasa;

class RasaIntent
{
    /* @var string */
    private $label;

    /* @var float */
    private $confidence;

    public function __construct($intent)
    {
        $this->label = $intent->name;
        $this->confidence = floatval($intent->confidence);
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return float
     */
    public function getConfidence()
    {
        return $this->confidence;
    }
}
