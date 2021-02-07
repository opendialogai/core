<?php
namespace OpenDialogAi\Core\Conversation;


class Intent extends ConversationObject
{
    protected Turn $turn;

    public function __construct(Turn $turn)
    {
        parent::__construct();
        $this->turn = $turn;
    }

    public function getTurn(): Turn
    {
        return $this->turn;
    }
}
