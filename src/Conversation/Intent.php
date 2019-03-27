<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Graph\Node\Node;

/**
 * The intent of an utterance.
 */
class Intent extends Node
{
    private $completes = false;


    /**
     * Intent constructor.
     * @param $id
     * @param bool $completes
     * @throws UnsupportedAttributeTypeException
     *
     * @todo intents need unique identifiers in addition to the label that representes the intent.
     */
    public function __construct($id, $completes = false)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::INTENT));

        $this->setCompletesAttribute($completes);
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
                $this->attributes->put(Model::ORDER, $attribute);
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
