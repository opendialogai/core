<?php


namespace OpenDialogAi\Core\Conversation;


class Transition
{
    protected ?string $conversation;
    protected ?string $scene;
    protected ?string $turn;

    public function __construct(?string $conversation, ?string $scene, ?string $turn)
    {
        $this->conversation = $conversation;
        $this->scene = $scene;
        $this->turn = $turn;
    }

    public function getConversation(): ?string
    {
        return $this->conversation;
    }

    public function getScene(): ?string
    {
        return $this->scene;
    }

    public function getTurn(): ?string
    {
        return $this->turn;
    }
}
