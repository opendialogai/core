<?php

namespace OpenDialogAi\MessageBuilder\Message;

class MetaMessage
{
    public $data;

    /**
     * MetaMessage constructor.
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
            $messageMarkUp .= sprintf('<data name="%s">%s</data>', $key, $value);
        }

        return <<<EOT
<meta-message>
    $messageMarkUp
</meta-message>
EOT;
    }
}
