<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

abstract class WebChatFormElement
{
    private $name = null;

    /** @var string The display name of the form element */
    private $display = null;

    private $required = false;

    /**
     * @param $name
     * @param $display
     * @param $required
     */
    public function __construct($name, $display, $required = false)
    {
        $this->name = $name;
        $this->display = $display;
        $this->required = $required;
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
     * @return array
     */
    public function getData()
    {
        return [
            'name' => $this->getName(),
            'display' => $this->getDisplay(),
            'required' => $this->getRequired()
        ];
    }
}
