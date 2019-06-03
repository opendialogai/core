<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;

use Ds\Map;
use OpenDialogAi\Core\Conversation\Condition\Condition;
use OpenDialogAi\Core\Conversation\Intent;

class OpeningIntent
{
    private $intentId;

    private $intentUid;

    private $conversationId;

    private $conversationUid;

    private $order;

    private $confidence;

    private $interpreter;

    /* @var Intent */
    private $interpretedIntent;

    /* @var Map */
    private $conditions;


    public function __construct(
        $intentId,
        $intentUid,
        $conversationId,
        $conversationUid,
        $order,
        float $confidence = 1,
        $interpreter = null
    ) {
        $this->intentId = $intentId;
        $this->intentUid = $intentUid;
        $this->conversationId = $conversationId;
        $this->conversationUid = $conversationUid;
        $this->order = $order;
        $this->confidence = $confidence;
        $this->interpreter = $interpreter;
        $this->conditions = new Map();
    }

    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions->put($condition->getId(), $condition);
    }

    /**
     * @return Map
     */
    public function getConditions(): Map
    {
        return $this->conditions;
    }

    /**
     * @param Map $conditions
     */
    public function setConditions(Map $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return bool
     */
    public function hasConditions()
    {
        if (count($this->conditions) >= 1) {
            return true;
        }

        return false;
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
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order): void
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getConfidence(): float
    {
        return $this->confidence;
    }

    /**
     * @param int $confidence
     */
    public function setConfidence(float $confidence): void
    {
        $this->confidence = $confidence;
    }

    /**
     * @return Intent|null
     */
    public function getInterpretedIntent()
    {
        return $this->interpretedIntent;
    }

    /**
     * @param Intent $intent
     */
    public function setInterpretedIntent(Intent $intent)
    {
        $this->interpretedIntent = $intent;
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
