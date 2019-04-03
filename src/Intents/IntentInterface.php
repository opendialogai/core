<?php

namespace OpenDialogAi\Core\Intents;
use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * Definition of an Intent in the context of interpreting what a user has said in an incoming utterance
 */
interface IntentInterface
{
    /**
     * The confidence level to which the intent was interpreted. The higher the number, the more confident we can be
     * the the utterance maps to this intent
     *
     * @return float
     */
    public function getConfidence() : float;

    /**
     * The intent label. Should be in the format intent.{namespace}.{label}
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Returns any attributes that were contained in the source utterance
     *
     * @return AttributeBag
     */
    public function getAttributes() : AttributeBag;

    /**
     * Adds the given attribute to the Intent
     *
     * @param AttributeInterface $attribute
     * @return IntentInterface
     */
    public function addAttribute(AttributeInterface $attribute) : IntentInterface;
}
