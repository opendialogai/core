<?php

namespace OpenDialogAi\InterpreterEngine\Luis;

class LuisIntent
{
    /* @var string */
    private $label;

    /* @var float */
    private $confidence;

    public function __construct($intent)
    {
        $this->label = $intent->intent;
        $this->confidence = floatval($intent->score);
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
