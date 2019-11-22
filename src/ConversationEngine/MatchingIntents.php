<?php

namespace OpenDialogAi\ConversationEngine;

use Ds\Set;
use OpenDialogAi\Core\Conversation\Intent;

/**
 * A wrapper for matching intents. Holds a map of all intents and holds the logic for selecting the best match
 */
class MatchingIntents implements \Countable
{
    /** @var Set */
    private $intentsSet;

    public function __construct()
    {
        $this->intentsSet = new Set();
    }

    /**
     * Adds an intent to the map
     *
     * @param Intent $intent
     * @return MatchingIntents
     */
    public function addMatchingIntent(Intent $intent): MatchingIntents
    {
        $this->intentsSet->add($intent);
        return $this;
    }

    /**
     * Returns the best fitting intent from the map of all matches intents
     * For now, this just returns the first in the list
     *
     * @return Intent
     */
    public function getBestMatch(): Intent
    {
        return $this->intentsSet->first();
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->intentsSet->count();
    }
}
