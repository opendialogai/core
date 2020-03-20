<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

abstract class FormElement
{
    protected $name = null;

    /** @var string The display name of the form element */
    protected $display = null;

    protected $required = false;

    protected $defaultValue = '';

    /**
     * @param $name
     * @param $display
     * @param $required
     */
    public function __construct($name, $display, $required = false, $defaultValue = '')
    {
        $this->name = $name;
        $this->display = $display;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
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
     * @return array
     */
    public function getData()
    {
        $data = [
            'name' => $this->getName(),
            'display' => $this->getDisplay(),
            'required' => $this->getRequired()
        ];

        if ($defaultValue = $this->getDefaultValue()) {
            $data['default_value'] = $defaultValue;
        }

        return $data;
    }
}
