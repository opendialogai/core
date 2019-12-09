<?php

namespace OpenDialogAi\MessageBuilder\Message;

class EmptyMessage
{
    public function getMarkUp()
    {
        return <<<EOT
<empty-message></empty-message>
EOT;
    }
}
