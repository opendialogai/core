<?php

namespace OpenDialogAi\Core\Intents;

use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeInterface;

class Intent implements IntentInterface
{
    /** @var string */
    private $label;

    /** @var float */
    private $confidence;

    /** @var AttributeBag */
    private $attributes;

    /**
     * Intent constructor.
     * @param string $label
     * @param float $confidence
     */
    public function __construct(string $label, float $confidence)
    {
        $this->label = $label;
        $this->confidence = $confidence;
        $this->attributes = new AttributeBag();
    }

    /**
     * @inheritdoc
     */
    public function getConfidence(): float
    {
       return $this->confidence;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): AttributeBag
    {
        return $this->attributes;
    }

    /**
     * Adds the given attribute to the Intent
     *
     * @param AttributeInterface $attribute
     * @return IntentInterface
     */
    public function addAttribute(AttributeInterface $attribute): IntentInterface
    {
        $this->attributes->addAttribute($attribute);
        return $this;
    }
}
