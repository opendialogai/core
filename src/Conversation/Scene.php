<?php

namespace OpenDialogAi\Core\Conversation;


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
        parent::__construct();
        $this->setId($id);

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
        $this->user->says($intent);
    }

    public function userListensToBotFromOtherScene(Intent $intent)
    {
        $this->user->listensFor($intent);
    }


    public function botSaysToUser(Intent $intent)
    {
        $this->bot->says($intent);
        $this->user->listensFor($intent);
    }

    public function botSaysToUserLeadingOutOfScene(Intent $intent)
    {
        $this->bot->says($intent);
    }

    public function botListensToUserFromOtherScene(Intent $intent)
    {
        $this->bot->listensFor($intent);
    }


    public function getIntentsSaidByUser()
    {
        // @todo
    }

    public function getIntentsSaidByBot()
    {
        // @todo
    }

    public function getIntentsListenedByUser()
    {
        // @todo
    }

    public function getIntentsListenedByBot()
    {
        // @todo
    }

    public function getAllIntents()
    {
        // @todo
    }
}
