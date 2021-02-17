<?php


namespace OpenDialogAi\Core\Conversation\Tests;


use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

class IntentGenerator
{

    public static function generateIntents(): IntentCollection
    {
        $intents = new IntentCollection();

        $intents->addObject(Intent::createIntent('intent.core.I1', 1));
        $intents->addObject(Intent::createIntent('intent.core.I2', 1));
        $intents->addObject(Intent::createIntent('intent.core.I3', 1));
        $intents->addObject(Intent::createIntent('intent.core.I4', 1));
        $intents->addObject(Intent::createIntent('intent.core.I5', 1));

        return $intents;
    }

}
