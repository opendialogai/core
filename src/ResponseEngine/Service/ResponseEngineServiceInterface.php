<?php

namespace OpenDialogAi\ResponseEngine\Service;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolverService;

/**
 * Defines the Response Engine Servie
 */
interface ResponseEngineServiceInterface
{
    const ATTRIBUTE_OPERATION_KEY = 'operation';

    /**
     * Gets messages from the given intent formatted correctly for the platform the user is on
     *
     * @param string $intentName
     * @return array
     */
    public function getMessageForIntent(string $intentName) : array;

    /**
     * @param $text string The message test to fill
     * @return string The message text with attributes filled
     */
    public function fillAttributes($text) : string;

    /**
     * Sets the Attribute Resolver dependency to use
     *
     * @param AttributeResolverService $attributeResolverService
     */
    public function setAttributeResolver(AttributeResolverService $attributeResolverService) : void;
}
