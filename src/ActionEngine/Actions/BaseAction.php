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

    /** @var array|string[] */
    protected static array $requiredAttributes = [];

    /** @var array|string[] */
    protected static array $outputAttributes = [];

    /** @var Map */
    private $inputAttributes = [];

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
    public static function getRequiredAttributes(): array
    {
        return static::$requiredAttributes;
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
    public static function getOutputAttributes(): array
    {
        return static::$outputAttributes;
    }

    /**
     * @inheritdoc
     */
    public function outputsAttribute($attributeName): bool
    {
        return $this->outputAttributes->hasKey($attributeName);
    }
}
