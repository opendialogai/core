<?php

namespace OpenDialogAi\ResponseEngine;

use DateTime;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;

class MySqlLinkClick implements LinkClickInterface
{
    public function save(UtteranceAttribute $utterance): LinkClick
    {
        /** @var WebchatUrlClickUtterance $utterance */
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
