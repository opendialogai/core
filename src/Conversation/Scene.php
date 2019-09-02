<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\Core\Attribute\StringAttribute;

/**
 * A scene is a specific context of a conversation with the associated exchange of utterances between participants.
 */
class Scene extends NodeWithConditions
{

    /* @var Participant $bot - the bot participating in this scene */
    private $bot;

    /* @var Participant $user - the user participating in this scene */
    private $user;

    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::SCENE));

        // Create the scene participants
        $this->bot = new Participant($this->botIdInScene());
        $this->user = new Participant($this->userIdInScene());

        $this->createOutgoingEdge(Model::HAS_BOT_PARTICIPANT, $this->bot);
        $this->createOutgoingEdge(Model::HAS_USER_PARTICIPANT, $this->user);
    }

    public function botIdInScene()
    {
        return Model::BOT . '_in_' . $this->getId();
    }

    public function userIdInScene()
    {
        return Model::USER . '_in_' . $this->getId();
    }

    public function getBot()
    {
        return $this->bot;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function userSaysToBot(Intent $intent)
    {
        $this->user->says($intent);
        $this->bot->listensFor($intent);
    }

    public function userSaysToBotLeadingOutOfScene(Intent $intent)
    {
        $this->user->saysAcrossScenes($intent);
    }

    public function userListensToBotFromOtherScene(Intent $intent)
    {
        $this->user->listensForAcrossScenes($intent);
    }


    public function botSaysToUser(Intent $intent)
    {
        $this->bot->says($intent);
        $this->user->listensFor($intent);
    }

    public function botSaysToUserLeadingOutOfScene(Intent $intent)
    {
        $this->bot->saysAcrossScenes($intent);
    }

    public function botListensToUserFromOtherScene(Intent $intent)
    {
        $this->bot->listensForAcrossScenes($intent);
    }

    public function getIntentsSaidByUser()
    {
        return $this->user->getAllIntentsSaid();
    }

    public function getIntentsSaidByBot()
    {
        return $this->bot->getAllIntentsSaid();
    }

    public function getIntentsSaidByUserInOrder()
    {
        return $this->user->getAllIntentsSaidInOrder();
    }

    public function getIntentsSaidByBotInOrder()
    {
        return $this->bot->getAllIntentsSaidInOrder();
    }

    public function getIntentsListenedByUser()
    {
        return $this->user->getAllIntentsListenedFor();
    }

    public function getIntentsListenedByBot()
    {
        return $this->bot->getAllIntentsListenedFor();
    }

    public function getAllIntents(): Map
    {
        $allIntents = new Map();
        $allIntents = $allIntents->merge($this->getIntentsSaidByUser());
        $allIntents = $allIntents->merge($this->getIntentsSaidByBot());
        return $allIntents;
    }

    public function getIntentByOrder($order):Intent
    {
        $intents =  $this->getAllIntents()->filter( function ($key, $value) use ($order) {
            /* @var Intent $value */
            if ($value->getOrder() == $order) {
                return true;
            }
        });

        return $intents->first()->value;
    }

    /**
     * Checks whether the given intent is in the this scene
     *
     * @param Intent $intent
     * @return bool
     */
    private function hasIntent(Intent $intent)
    {
        return $this->getAllIntents()->hasKey($intent->getId());
    }

    /**
     * Get the bot intents said in the scene that have a higher order than the current intent
     * and are in an uninterrupted ascending order.
     * @param Intent $currentIntent
     * @return Map
     */
    public function getNextPossibleBotIntents(Intent $currentIntent): Map
    {
        // If the current intent is in this scene, use its order. If it's said across scenes, we use 0.
        $currentOrder = $this->hasIntent($currentIntent) ? $currentIntent->getOrder() : 0;

        /** @var Intent $previousKeptIntent */
        $previousKeptIntent = null;

        $intents = $this->getIntentsSaidByBotInOrder()->filter(
            function ($key, Intent $possibleIntent) use ($currentOrder, &$previousKeptIntent) {
                // Intents are considered sequential if its the first or if it directly follows the previously kept intent
                $intentsAreSequential = is_null($previousKeptIntent)
                    || $previousKeptIntent->getOrder() + 1 == $possibleIntent->getOrder();

                $shouldKeep = $possibleIntent->getOrder() > $currentOrder && $intentsAreSequential;

                if ($shouldKeep) {
                    $previousKeptIntent = $possibleIntent;
                }

                return $shouldKeep;
            }
        );

        return $intents;
    }

    /**
     * Get the user intents said in the scene that have a higher order than the current intent
     * and are in an uninterrupted ascending order.
     * @param Intent $currentIntent
     * @return Map
     */
    public function getNextPossibleUserIntents(Intent $currentIntent): Map
    {
        $currentOrder = $currentIntent->getOrder();

        $intents = $this->getIntentsSaidByUser()->filter( function ($key, $value) use ($currentOrder) {
            /* @var Intent $value */
            if ($value->getOrder() > $currentOrder) {
                return true;
            }
        });

        return $intents;
    }
}
