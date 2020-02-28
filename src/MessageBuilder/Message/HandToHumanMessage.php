<?php

namespace OpenDialogAi\MessageBuilder\Message;

class HandToHumanMessage
{
    public $data;

    /**
     * HandToHumanMessage constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getMarkUp()
    {
        $messageMarkUp = '';

        foreach ($this->data as $key => $value) {
            $messageMarkUp = sprintf('<data name="%s">%s</data>', $key, $value);
        }

        return <<<EOT
<hand-to-human-message>
    $messageMarkUp
</hand-to-human-message>
EOT;
    }
}
