<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

abstract class FormElement
{
    protected $name = null;

    /** @var string The display name of the form element */
    protected $display = null;

    protected $required = false;

    protected $defaultValue = '';

    protected $min;

    protected $max;

    /**
     * @param $name
     * @param $display
     * @param bool $required
     * @param string $defaultValue
     * @param null $min
     * @param null $max
     */
    public function __construct($name, $display, $required = false, $defaultValue = '', $min = null, $max = null)
    {
        $this->name = $name;
        $this->display = $display;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * @param $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param mixed $min
     * @return FormElement
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     * @return FormElement
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [
            'name' => $this->getName(),
            'display' => $this->getDisplay(),
            'required' => $this->getRequired(),
            'min' => $this->getMin(),
            'max' => $this->getMax()
        ];

        if ($defaultValue = $this->getDefaultValue()) {
            $data['default_value'] = $defaultValue;
        }

        return $data;
    }
}
