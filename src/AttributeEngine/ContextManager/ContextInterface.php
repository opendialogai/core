<?php


namespace OpenDialogAi\AttributeEngine\ContextManager;


use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;

/**
 * Class ContextInterface
 * @package OpenDialogAi\AttributeEngine\ContextManager
 *
 * A context is a semantically-related grouping of Attributes. It provides
 * a simpler way for other components of OpenDialog to extract related context
 * without having to ask for each individual attribute.
 */
interface ContextInterface
{
    public function getAttributes(): Map;

    public function getAttribute(string $attributeId): AttributeInterface;

    public function addAttribute(AttributeInterface $attribute);
}
