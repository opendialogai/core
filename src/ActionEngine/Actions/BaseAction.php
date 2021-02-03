<?php

namespace OpenDialogAi\ActionEngine\Actions;

use Ds\Map;
use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\Core\Traits\HasName;

abstract class BaseAction extends BaseOpenDialogComponent implements ActionInterface
{
    use HasName;

    protected static string $componentType = BaseOpenDialogComponent::ACTION_COMPONENT_TYPE;
    protected static string $componentSource = BaseOpenDialogComponent::APP_COMPONENT_SOURCE;

    protected static $name = 'action.core.base';

    /** @var string[] */
    protected $requiredAttributes = [];

    /** @var Map */
    protected $inputAttributes = [];

    /** @var Map */
    protected $outputAttributes = [];

    /**
     * @inheritdoc
     */
    public function setInputAttributes($inputAttributes)
    {
        $this->inputAttributes = $inputAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getRequiredAttributes(): array
    {
        return $this->requiredAttributes;
    }

    /**
     * @inheritdoc
     */
    public function requiresAttribute($attributeName): bool
    {
        return in_array($attributeName, $this->requiredAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getInputAttributes(): Map
    {
        return $this->inputAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getOutputAttributes(): Map
    {
        return $this->outputAttributes;
    }

    /**
     * @inheritdoc
     */
    public function outputsAttribute($attributeName): bool
    {
        return $this->outputAttributes->hasKey($attributeName);
    }
}
