<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * The intent of an utterance.
 */
class Intent extends Node
{
    const CONFIDENCE = 'intent_confidence';

    private $completes = false;

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
    public static function createIntentWithConfidence(string $label, float $confidence)
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
    public function setConfidence($confidence)
    {
        $this->addAttribute(new FloatAttribute(self::CONFIDENCE, $confidence));
        return $this;
    }

    /**
     * Gets the set confidence value the intent
     *
     * @return float
     */
    public function getConfidence()
    {
        $confidence = $this->getAttribute(self::CONFIDENCE);
        return $confidence->getValue();
    }

    /**
     * Gets the label value of the intent
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->id;
    }

    /**
     * @param $order
     * @return $this|bool
     */
    public function setOrderAttribute($order)
    {
        try {
            $attribute = new IntAttribute(Model::ORDER, $order);
        } catch (UnsupportedAttributeTypeException $e) {
            return false;
        }
        $this->attributes->put(Model::ORDER, $attribute);
        return $this;
    }

    /**
     * @param bool $completes
     * @return Intent | bool
     */
    public function setCompletesAttribute($completes = false)
    {
        $this->completes = $completes;

        // Check if we've already set the attribute and change it's value if so.
        if ($this->attributes->hasKey(Model::COMPLETES)) {
            /* @var BooleanAttribute $attribute */
            $attribute = $this->attributes->get(Model::COMPLETES);
            $attribute->setValue($this->completes);
            $this->attributes->put(Model::COMPLETES, $attribute);
            return $this;
        } else {
            try {
                $attribute = new BooleanAttribute(Model::COMPLETES, $this->completes);
                $this->addAttribute($attribute);
                return $this;
            } catch (UnsupportedAttributeTypeException $e) {
                return false;
            }
        }
    }

    public function causesAction(Action $action)
    {
        $this->createOutgoingEdge(Model::CAUSES_ACTION, $action);
    }
}
