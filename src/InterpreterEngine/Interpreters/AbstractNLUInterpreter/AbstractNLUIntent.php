<?php


namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

abstract class AbstractNLUIntent
{
    /* @var float */
    protected $confidence;

    /* @var string */
    protected $label;

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
