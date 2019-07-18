<?php

namespace OpenDialogAi\ResponseEngine;

use DateTime;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;

class MySqlLinkClick implements LinkClickInterface
{
    public function save(WebchatUrlClickUtterance $utterance): LinkClick
    {
        $timestamp = DateTime::createFromFormat('U.u', $utterance->getTimestamp())->format('Y-m-d H:i:s');

        $linkClick = new LinkClick([
            'user_id' => $utterance->getUserId(),
            'url' => $utterance->getData()['url'],
            'date' => $timestamp,
        ]);
        $linkClick->save();

        return $linkClick;
    }
}
