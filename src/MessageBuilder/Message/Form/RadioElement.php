<?php

namespace OpenDialogAi\MessageBuilder\Message\Form;

class RadioElement extends BaseElement
{
    public $name;
    public $display;
    public $options;

    /**
     * RadioElement constructor.
     * @param $name
     * @param $display
     * @param $options
     */
    public function __construct($name, $display, $options)
    {
        $this->name = $name;
        $this->display = $display;
        $this->options = $options;
    }

    public function getMarkUp()
    {
        $optionsMarkUp = '';

        foreach ($this->options as $option) {
            $optionsMarkUp .= '<option><key>' . $option['key'] . '</key>';
            $optionsMarkUp .= '<value>' . $option['value'] . '</value></option>';
        }

        return <<<EOT
<element>
    <element_type>radio</element_type>
    <name>$this->name</name>
    <display>$this->display</display>
    <options>$optionsMarkUp</options>
</element>
EOT;
    }
}
