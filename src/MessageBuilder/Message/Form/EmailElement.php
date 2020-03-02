<?php

namespace OpenDialogAi\MessageBuilder\Message\Form;

class EmailElement extends BaseElement
{
    public $name;
    public $display;
    public $required;

    /**
     * EmailElement constructor.
     * @param $name
     * @param $display
     * @param $required
     */
    public function __construct($name, $display, $required)
    {
        $this->name = $name;
        $this->display = $display;
        $this->required = ($required) ? 'true' : 'false';
    }

    public function getMarkUp()
    {
        return <<<EOT
<element>
    <element_type>email</element_type>
    <name>$this->name</name>
    <display>$this->display</display>
    <required>$this->required</required>
</element>
EOT;
    }
}
