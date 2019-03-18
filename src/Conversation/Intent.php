<?php

namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\UnsupportedAttributeTypeException;
use OpenDialogAi\Core\Graph\Node\Node;

class Intent extends Node
{
    private $completes = false;

    public function __construct($id, $completes = false)
    {
        parent::__construct();
        $this->setId($id);
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
}
