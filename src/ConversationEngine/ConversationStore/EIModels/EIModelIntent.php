<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;

use Ds\Map;
use Ds\Pair;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;

class EIModelIntent extends EIModelBase
{
    private $intentId;

    private $intentUid;

    private $conversationId;

    private $conversationUid;

    private $order;

    private $confidence;

    private $completes;

    private $nextScene;

    /* @var Pair $action */
    private $action;

    /* @var Pair $interpreter */
    private $interpreter;

    /* @var Map $expectedAttributes */
    private $expectedAttributes;

    /* @var Map $inputActionAttributes */
    private $inputActionAttributes;

    /* @var Map $outputActionAttributes */
    private $outputActionAttributes;

    /* @var Intent */
    private $interpretedIntent;

    /* @var Map */
    private $conditions;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param null $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        if (is_null($additionalParameter)) {
            // If there is no additional parameter, presume we are dealing with just an intent
            $eiType = EIModelBase::hasEIType($response, Model::INTENT);
            $intentResponse = $response;
        } else {
            // Otherwise presume we are dealing with a conversation response and an intent
            $eiType =  EIModelBase::hasEIType($response, Model::CONVERSATION_TEMPLATE, Model::CONVERSATION_USER);
            $intentResponse = $additionalParameter;
        }

        return $eiType
            && key_exists(Model::ID, $intentResponse)
            && key_exists(Model::UID, $intentResponse)
            && key_exists(Model::ORDER, $intentResponse);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param $additionalParameter
     * @return EIModel
     * @throws Exception
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $intentResponse = is_null($additionalParameter) ? $response : $additionalParameter;

        $intent = new self();

        $intent->setIntentId($intentResponse[Model::ID]);
        $intent->setIntentUid($intentResponse[Model::UID]);
        $intent->setOrder($intentResponse[Model::ORDER]);
        $intent->setConfidence(isset($intentResponse[Model::CONFIDENCE]) ? $intentResponse[Model::CONFIDENCE] : 1);
        $intent->setConditions(new Map());

        if (!is_null($additionalParameter)) {
            // If there is an additional parameter it means that $response contains the conversation data
            $intent->setConversationId($response[Model::ID]);
            $intent->setConversationUid($response[Model::UID]);
        }

        $intent->setCompletes(isset($intentResponse[Model::COMPLETES]) ? (bool) $intentResponse[Model::COMPLETES] : false);

        if (isset($intentResponse[Model::CAUSES_ACTION])) {
            $intent->setAction(
                new Pair($intentResponse[Model::CAUSES_ACTION][0][Model::ID],
                    $intentResponse[Model::CAUSES_ACTION][0][Model::UID]
                )
            );
        }

        if (isset($intentResponse[Model::HAS_INTERPRETER])) {
            $intent->setInterpreter(
                new Pair(
                    $intentResponse[Model::HAS_INTERPRETER][0][Model::ID],
                    $intentResponse[Model::HAS_INTERPRETER][0][Model::UID]
                )
            );
        }

        $intent->expectedAttributes = new Map();
        if (isset($intentResponse[Model::HAS_EXPECTED_ATTRIBUTE])) {
            foreach ($intentResponse[Model::HAS_EXPECTED_ATTRIBUTE] as $expectedAttribute) {
                $intent->setExpectedAttribute(
                    $expectedAttribute[Model::ID],
                    $expectedAttribute[Model::UID]
                );
            }
        }

        $intent->inputActionAttributes = new Map();
        if (isset($intentResponse[Model::HAS_INPUT_ACTION_ATTRIBUTE])) {
            foreach ($intentResponse[Model::HAS_INPUT_ACTION_ATTRIBUTE] as $inputActionAttribute) {
                $intent->setInputActionAttribute(
                    $inputActionAttribute[Model::ID],
                    $inputActionAttribute[Model::UID]
                );
            }
        }

        $intent->outputActionAttributes = new Map();
        if (isset($intentResponse[Model::HAS_OUTPUT_ACTION_ATTRIBUTE])) {
            foreach ($intentResponse[Model::HAS_OUTPUT_ACTION_ATTRIBUTE] as $outputActionAttribute) {
                $intent->setOutputActionAttribute(
                    $outputActionAttribute[Model::ID],
                    $outputActionAttribute[Model::UID]
                );
            }
        }

        if (isset($intentResponse[Model::LISTENED_BY_FROM_SCENES])) {
            $intent->setNextScene(self::getEndingSceneId($intentResponse));
        }

        return $intent;
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
     * @return Pair|null
     */
    public function getInterpreter(): ?Pair
    {
        return $this->interpreter;
    }

    /**
     * @return string|null
     */
    public function getInterpreterId(): ?string
    {
        return is_null($this->interpreter) ? null : $this->interpreter->key;
    }

    /**
     * @param Pair $interpreter
     */
    public function setInterpreter(Pair $interpreter): void
    {
        $this->interpreter = $interpreter;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    /**
     * @return float
     */
    public function getConfidence(): float
    {
        return $this->confidence;
    }

    /**
     * @param float $confidence
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
        return isset($this->interpreter);
    }

    /**
     * @return bool
     */
    public function getCompletes(): bool
    {
        return $this->completes;
    }

    /**
     * @param bool $completes
     */
    public function setCompletes(bool $completes): void
    {
        $this->completes = $completes;
    }

    /**
     * @return Pair|null
     */
    public function getAction(): ?Pair
    {
        return $this->action;
    }

    /**
     * @param Pair $action
     */
    public function setAction(Pair $action): void
    {
        $this->action = $action;
    }

    /**
     * @return string|null
     */
    public function getNextScene(): ?string
    {
        return $this->nextScene;
    }

    /**
     * @param $nextScene string
     */
    public function setNextScene($nextScene): void
    {
        $this->nextScene = $nextScene;
    }

    /**
     * @return bool
     */
    public function hasExpectedAttributes(): bool
    {
        return $this->expectedAttributes->count() > 0;
    }

    /**
     * @return bool
     */
    public function hasInputActionAttributes(): bool
    {
        return $this->inputActionAttributes->count() > 0;
    }

    /**
     * @return bool
     */
    public function hasOutputActionAttributes(): bool
    {
        return $this->outputActionAttributes->count() > 0;
    }

    /**
     * @return Map
     */
    public function getExpectedAttributes(): Map
    {
        return $this->expectedAttributes;
    }

    /**
     * @return Map
     */
    public function getInputActionAttributes(): Map
    {
        return $this->inputActionAttributes;
    }

    /**
     * @return Map
     */
    public function getOutputActionAttributes(): Map
    {
        return $this->outputActionAttributes;
    }

    public function setExpectedAttribute($id, $uid): void
    {
        $this->expectedAttributes->put($uid, $id);
    }

    public function setInputActionAttribute($id, $uid): void
    {
        $this->inputActionAttributes->put($uid, $id);
    }

    public function setOutputActionAttribute($id, $uid): void
    {
        $this->outputActionAttributes->put($uid, $id);
    }

    /**
     * Returns the expected attributes split out by context. Will return map with attribute names as keys and their
     * associated context names as values
     */
    public function getExpectedAttributeContexts()
    {
        $attributesContexts = new Map();

        foreach ($this->expectedAttributes as $expectedAttribute) {
            $attributesContexts->put(
                ContextParser::determineAttributeId($expectedAttribute),
                ContextParser::determineContextId($expectedAttribute)
            );
        }

        return $attributesContexts;
    }

    /**
     * Returns the input action attributes split out by context.
     * Will return map with attribute names as keys and their associated context names as values
     *
     * @return Map
     */
    public function getInputActionAttributeContexts()
    {
        $attributesActionContexts = new Map();
        foreach ($this->inputActionAttributes as $inputActionAttribute) {
            $attributesActionContexts->put(
                ContextParser::determineAttributeId($inputActionAttribute),
                ContextParser::determineContextId($inputActionAttribute)
            );
        }
        return $attributesActionContexts;
    }

    /**
     * Returns the output action attributes split out by context.
     * Will return map with attribute names as keys and their associated context names as values
     *
     * @return Map
     */
    public function getOutputActionAttributeContexts()
    {
        $attributesActionContexts = new Map();
        foreach ($this->outputActionAttributes as $outputActionAttribute) {
            $attributesActionContexts->put(
                ContextParser::determineAttributeId($outputActionAttribute),
                ContextParser::determineContextId($outputActionAttribute)
            );
        }
        return $attributesActionContexts;
    }

    /**
     * @param $intentData
     * @return mixed
     * @throws Exception
     */
    public static function getEndingSceneId($intentData)
    {
        $listenedBy = $intentData[Model::LISTENED_BY_FROM_SCENES][0];

        if (isset($listenedBy[Model::USER_PARTICIPATES_IN][0][Model::ID])) {
            return $listenedBy[Model::USER_PARTICIPATES_IN][0][Model::ID];
        }

        if (isset($listenedBy[Model::BOT_PARTICIPATES_IN][0][Model::ID])) {
            return $listenedBy[Model::BOT_PARTICIPATES_IN][0][Model::ID];
        }

        Log::error('Could not extract ending scene id', $listenedBy);
        throw new Exception('Could not extract ending scene id');
    }
}
