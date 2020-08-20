<?php

namespace OpenDialogAi\MessageBuilder\Message;

class HandToSystemMessage
{
    public $data;

    public $system;

    /**
     * HandToSystemMessage constructor.
     * @param $system
     * @param $data
     */
    public function __construct($system, $data)
    {
        $this->data = $data;
        $this->system = $system;
    }

    public function getMarkUp()
    {
        $messageMarkUp = '';

        foreach ($this->data as $key => $value) {
            $messageMarkUp .= sprintf('<data name="%s">%s</data>', $key, $value);
        }

        return <<<EOT
<hand-to-system-message system="$this->system">
    $messageMarkUp
</hand-to-system-message>
EOT;
    }
}
