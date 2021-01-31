<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Exceptions\UnsupportedAttributeTypeException;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;

/**
 * The intent of an utterance.
 */
class Intent extends NodeWithConditions
{
    protected static $idIsUnique = false;

    public static $coreAttributes = [
        Model::EI_TYPE,
        Model::COMPLETES,
        Model::ORDER,
        Model::CONFIDENCE,
        Model::REPEATING
    ];

    private $completes = false;

    /** @var int */
    private $order;

    /**
     * Intent constructor.
     * @param $id
     * @param bool $completes
     * @throws \OpenDialogAi\AttributeEngine\Exceptions\UnsupportedAttributeTypeException
     */
    public function __construct($id, $completes = false)
    {
        parent::__construct($id);
        $this->setGraphType(DGraphClient::INTENT);
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::INTENT));
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::REPEATING, false));

        $this->setCompletesAttribute($completes);
    }

    /**
     * Static helper function to create an intent with a confidence score
     *
     * @param string $label
     * @param float $confidence
     * @return Intent
     */
    public static function createIntentWithConfidence(string $label, float $confidence): Intent
    {
        $intent = new self($label);
        $intent->setConfidence($confidence);

        return $intent;
    }

    /**
     * Sets the confidence of the intent as an attribute
     *
     * @param $confidence
     * @return Intent
     */
    public function setConfidence($confidence): Intent
    {
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::CONFIDENCE, $confidence));
        return $this;
    }

    /**
     * Gets the set confidence value the intent
     *
     * @return float
     */
    public function getConfidence(): float
    {
        if ($this->hasAttribute(Model::CONFIDENCE)) {
            $confidence = $this->getAttribute(Model::CONFIDENCE);
            return $confidence->getValue();
        }

        return 1;
    }

    /**
     * Gets the label value of the intent
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->id;
    }

    /**
     * Returns a map of all non-core attributes on the Intent - ie those not found in @see $coreAttributes
     *
     * @return Map
     */
    public function getNonCoreAttributes(): Map
    {
        return $this->attributes->filter(function ($key, $value) {
            if (!in_array($key, self::$coreAttributes)) {
                return true;
            }

            return false;
        });
    }

    /**
     * @param $order
     * @return $this|bool
     */
    public function setOrderAttribute($order)
    {
        $this->order = $order;

        try {
            $attribute = AttributeResolver::getAttributeFor(Model::ORDER, $order);
        } catch (UnsupportedAttributeTypeException $e) {
            return false;
        }
        $this->attributes->put(Model::ORDER, $attribute);
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param bool $completes
     * @return Intent | bool
     */
    public function setCompletesAttribute($completes = false)
    {
        $this->completes = $completes;

        try {
            $attribute = AttributeResolver::getAttributeFor(Model::COMPLETES, $this->completes);
            $this->addAttribute($attribute);
            return $this;
        } catch (UnsupportedAttributeTypeException $e) {
            return false;
        }
    }

    /**
     * @param Action $action
     */
    public function addAction(Action $action): void
    {
        $this->createOutgoingEdge(Model::CAUSES_ACTION, $action);
    }

    /**
     * @param Interpreter $interpreter
     */
    public function addInterpreter(Interpreter $interpreter): void
    {
        $this->createOutgoingEdge(Model::HAS_INTERPRETER, $interpreter);
    }

    /**
     * @return bool
     */
    public function hasInterpreter(): bool
    {
        if ($this->hasOutgoingEdgeWithRelationship(Model::HAS_INTERPRETER)) {
            return true;
        }

        return false;
    }

    /**
     * @return Interpreter
     * @throws NodeDoesNotExistException
     */
    public function getInterpreter(): Interpreter
    {
        if ($this->hasInterpreter()) {
            return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_INTERPRETER)->first()->value;
        }

        throw new NodeDoesNotExistException('Interpreter not set for Intent');
    }

    /**
     * @return bool
     */
    public function causesAction(): bool
    {
        if ($this->hasOutgoingEdgeWithRelationship(Model::CAUSES_ACTION)) {
            return true;
        }

        return false;
    }

    /**
     * @return Action
     * @throws NodeDoesNotExistException
     */
    public function getAction(): Action
    {
        if ($this->causesAction()) {
            return $this->getNodesConnectedByOutgoingRelationship(Model::CAUSES_ACTION)->first()->value;
        }

        throw new NodeDoesNotExistException('Action not set for Intent');
    }

    /**
     * @param ExpectedAttribute $expectedAttribute
     */
    public function addExpectedAttribute($expectedAttribute): void
    {
        $this->createOutgoingEdge(Model::HAS_EXPECTED_ATTRIBUTE, $expectedAttribute);
    }

    /**
     * @param ExpectedAttribute $inputActionAttribute
     */
    public function addInputActionAttribute($inputActionAttribute): void
    {
        $this->createOutgoingEdge(Model::HAS_INPUT_ACTION_ATTRIBUTE, $inputActionAttribute);
    }

    /**
     * @param ExpectedAttribute $outputActionAttribute
     */
    public function addOutputActionAttribute($outputActionAttribute): void
    {
        $this->createOutgoingEdge(Model::HAS_OUTPUT_ACTION_ATTRIBUTE, $outputActionAttribute);
    }

    /**
     * @return string[]
     * @throws NodeDoesNotExistException
     */
    public function getExpectedAttributes(): array
    {
        if ($this->hasExpectedAttributes()) {
            return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_EXPECTED_ATTRIBUTE)->values()->toArray();
        }

        throw new NodeDoesNotExistException('Intent has no expected attributes');
    }

    /**
     * @return string[]
     * @throws NodeDoesNotExistException
     */
    public function getInputActionAttributes(): array
    {
        if ($this->hasInputActionAttributes()) {
            return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_INPUT_ACTION_ATTRIBUTE)
                ->values()->toArray();
        }

        throw new NodeDoesNotExistException('Intent has no input action attributes');
    }

    /**
     * @return string[]
     * @throws NodeDoesNotExistException
     */
    public function getOutputActionAttributes(): array
    {
        if ($this->hasOutputActionAttributes()) {
            return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_OUTPUT_ACTION_ATTRIBUTE)
                ->values()->toArray();
        }

        throw new NodeDoesNotExistException('Intent has no output action attributes');
    }

    /**
     * @return bool
     */
    public function hasExpectedAttributes(): bool
    {
        return $this->hasOutgoingEdgeWithRelationship(Model::HAS_EXPECTED_ATTRIBUTE);
    }

    /**
     * @return bool
     */
    public function hasInputActionAttributes(): bool
    {
        return $this->hasOutgoingEdgeWithRelationship(Model::HAS_INPUT_ACTION_ATTRIBUTE);
    }

    /**
     * @return bool
     */
    public function hasOutputActionAttributes(): bool
    {
        return $this->hasOutgoingEdgeWithRelationship(Model::HAS_OUTPUT_ACTION_ATTRIBUTE);
    }

    /**
     * Returns the expected attributes split out by context. Will return map with attribute names as keys and their
     * associated context names as values
     *
     * @return Map
     */
    public function getExpectedAttributeContexts(): Map
    {
        $attributesContexts = new Map();

        try {
            /** @var ExpectedAttribute $expectedAttribute */
            foreach ($this->getExpectedAttributes() as $expectedAttribute) {
                $attributesContexts->put(
                    ContextParser::determineAttributeId($expectedAttribute->getId()),
                    ContextParser::determineContextId($expectedAttribute->getId())
                );
            }
        } catch (NodeDoesNotExistException $e) {
            Log::warning($e->getMessage());
        }

        return $attributesContexts;
    }

    /**
     * Returns the input action attributes split out by context.
     * Will return map with attribute names as keys and their associated context names as values
     *
     * @return Map
     */
    public function getInputActionAttributeContexts(): Map
    {
        $attributesActionContexts = new Map();

        try {
            /** @var ExpectedAttribute $inputActionAttribute */
            foreach ($this->getInputActionAttributes() as $inputActionAttribute) {
                $attributesActionContexts->put(
                    ContextParser::determineAttributeId($inputActionAttribute->getId()),
                    ContextParser::determineContextId($inputActionAttribute->getId())
                );
            }
        } catch (NodeDoesNotExistException $e) {
            Log::warning($e->getMessage());
        }

        return $attributesActionContexts;
    }

    /**
     * Returns the output action attributes split out by context.
     * Will return map with attribute names as keys and their associated context names as values
     *
     * @return Map
     */
    public function getOutputActionAttributeContexts(): Map
    {
        $attributesActionContexts = new Map();

        try {
            /** @var ExpectedAttribute $outputActionAttribute */
            foreach ($this->getOutputActionAttributes() as $outputActionAttribute) {
                $attributesActionContexts->put(
                    ContextParser::determineAttributeId($outputActionAttribute->getId()),
                    ContextParser::determineContextId($outputActionAttribute->getId())
                );
            }
        } catch (NodeDoesNotExistException $e) {
            Log::warning($e->getMessage());
        }

        return $attributesActionContexts;
    }

    public function completes(): bool
    {
        return $this->completes;
    }

    /**
     * Checks if the intent passed in matches this intent by checking id and confidence
     *
     * @param Intent $intent
     * @return bool
     */
    public function matches(Intent $intent): bool
    {
        return
            $this->getId() === $intent->getId() &&
            $this->getConfidence() >= $intent->getConfidence();
    }

    /**
     * Copies all non-core attributes from the passed intent to this intent
     *
     * @param Intent $intent
     */
    public function copyNonCoreAttributes(Intent $intent): void
    {
        foreach ($intent->getNonCoreAttributes() as $attribute) {
            $this->addAttribute($attribute);
        }
    }

    /**
     * @return Map
     */
    public function getAllConditions(): Map
    {
        $conditions = new Map();

        if ($this->hasConditions()) {
            $conditions = $conditions->merge($this->getConditions());
        }

        $conditions = $conditions->merge($this->getConditionsFromDestinationScene());

        return $conditions;
    }

    /**
     * @return Map
     */
    private function getConditionsFromDestinationScene(): Map
    {
        $conditions = new Map();

        if ($this->hasIncomingEdgeWithRelationship(Model::LISTENS_FOR_ACROSS_SCENES)) {
            /** @var Participant $participant */
            $participant = $this->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES)->first()->value;

            /** @var Scene $scene */
            if ($participant->hasIncomingEdgeWithRelationship(Model::HAS_USER_PARTICIPANT)) {
                $scene = $participant->getNodesConnectedByIncomingRelationship(Model::HAS_USER_PARTICIPANT)->first()->value;
            } elseif ($participant->hasIncomingEdgeWithRelationship(Model::HAS_BOT_PARTICIPANT)) {
                $scene = $participant->getNodesConnectedByIncomingRelationship(Model::HAS_BOT_PARTICIPANT)->first()->value;
            } else {
                return $conditions;
            }

            if ($scene->hasConditions()) {
                $conditions->putAll($scene->getConditions());
            }
        }

        return $conditions;
    }

    /**
     * @param VirtualIntent $virtualIntent
     */
    public function addVirtualIntent(VirtualIntent $virtualIntent): void
    {
        $this->createOutgoingEdge(Model::SIMULATES_INTENT, $virtualIntent);
    }

    /**
     * @return VirtualIntent|null
     */
    public function getVirtualIntent(): ?VirtualIntent
    {
        $nodes = $this->getNodesConnectedByOutgoingRelationship(Model::SIMULATES_INTENT);

        return $nodes->isEmpty() ? null : $nodes->first()->value;
    }

    /**
     * @return bool
     */
    public function hasFollowedBy(): bool
    {
        return $this->hasOutgoingEdgeWithRelationship(Model::FOLLOWED_BY);
    }

    /**
     * @return Intent
     */
    public function getFollowedBy(): Intent
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::FOLLOWED_BY)->first()->value;
    }

    /**
     * @param Intent $toIntent
     * @param $createdAt
     */
    public function setFollowedBy(Intent $toIntent, $createdAt): void
    {
        $this->createOutgoingEdge(Model::FOLLOWED_BY, $toIntent)
            ->addFacet(ModelFacets::CREATED_AT, $createdAt);
    }

    /**
     * @return bool
     */
    public function isRepeating(): bool
    {
        return $this->getAttributeValue(Model::REPEATING);
    }

    /**
     * @param bool $repeating
     */
    public function setRepeating(bool $repeating)
    {
        $this->setAttribute(Model::REPEATING, $repeating);
    }
}
