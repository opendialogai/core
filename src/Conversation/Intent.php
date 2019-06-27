<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Graph\Node\Node;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;

/**
 * The intent of an utterance.
 */
class Intent extends Node
{
    public static $coreAttributes = [
        Model::EI_TYPE,
        Model::COMPLETES,
        Model::ORDER,
        Model::CONFIDENCE
    ];

    private $completes = false;

    /** @var int */
    private $order;

    /**
     * Intent constructor.
     * @param $id
     * @param bool $completes
     * @throws UnsupportedAttributeTypeException
     *
     * @todo intents need unique identifiers in addition to the label that represents the intent.
     */
    public function __construct($id, $completes = false)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::INTENT));

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
        $this->addAttribute(new FloatAttribute(Model::CONFIDENCE, $confidence));
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
            $attribute = new IntAttribute(Model::ORDER, $order);
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
     * @return bool
     */
    public function hasExpectedAttributes(): bool
    {
        return $this->hasOutgoingEdgeWithRelationship(Model::HAS_EXPECTED_ATTRIBUTE);
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
            // nothing
        }

        return $attributesContexts;
    }

    public function completes(): bool
    {
        $this->completes;
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
}
