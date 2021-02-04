<?php


namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\AttributeEngine\Attributes\AttributeInterface;

interface AttributeEngineReflectionInterface extends \JsonSerializable
{
    /**
     * @return Map|AttributeInterface[]
     */
    public function getAvailableAttributes(): Map;

    /**
     * @return Map|AttributeInterface[]
     */
    public function getAvailableAttributeTypes(): Map;
}
