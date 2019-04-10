<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;


class OpeningIntent
{
    private $intentId;

    private $intentUid;

    private $conversationId;

    private $conversationUid;

    private $interpreter;

    public function __construct($intentId, $intentUid, $conversationId, $conversationUid, $interpreter = null)
    {
        $this->intentId = $intentId;
        $this->intentUid = $intentUid;
        $this->conversationId = $conversationId;
        $this->conversationUid = $conversationUid;
        $this->interpreter = $interpreter;
    }

    /**
     * @return mixed
     */
    public function getIntentId()
    {
        return $this->intentId;
    }

    /**
     * @param mixed $intentId
     */
    public function setIntentId($intentId): void
    {
        $this->intentId = $intentId;
    }

    /**
     * @return mixed
     */
    public function getIntentUid()
    {
        return $this->intentUid;
    }

    /**
     * @param mixed $intentUid
     */
    public function setIntentUid($intentUid): void
    {
        $this->intentUid = $intentUid;
    }

    /**
     * @return mixed
     */
    public function getConversationId()
    {
        return $this->conversationId;
    }

    /**
     * @param mixed $conversationId
     */
    public function setConversationId($conversationId): void
    {
        $this->conversationId = $conversationId;
    }

    /**
     * @return mixed
     */
    public function getConversationUid()
    {
        return $this->conversationUid;
    }

    /**
     * @param mixed $conversationUid
     */
    public function setConversationUid($conversationUid): void
    {
        $this->conversationUid = $conversationUid;
    }

    /**
     * @return mixed
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * @param mixed $interpreter
     */
    public function setInterpreter($interpreter): void
    {
        $this->interpreter = $interpreter;
    }

    /**
     * @return bool
     */
    public function hasInterpreter(): bool
    {
        if (isset($this->interpreter)) {
            return true;
        }

        return false;
    }


}