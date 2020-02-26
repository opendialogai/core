<?php

namespace OpenDialogAi\MessageBuilder\Message;

class FullPageRichMessage extends BaseRichMessage
{
    public function getMarkUp()
    {
        $buttonMarkUp = $this->getButtonMarkUp();

        $imageMarkUp = $this->getImageMarkUp();

        return <<<EOT
<fp-rich-message>
    <title>$this->title</title>
    <subtitle>$this->subtitle</subtitle>
    <text>$this->text</text>
    $buttonMarkUp
    $imageMarkUp
</fp-rich-message>
EOT;
    }
}
