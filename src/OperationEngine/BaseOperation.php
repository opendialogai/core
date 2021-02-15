<?php

namespace OpenDialogAi\OperationEngine;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;
use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\Core\Traits\HasName;

abstract class BaseOperation implements OperationInterface, OpenDialogComponent
{
    use ODComponent;

    protected static string $componentType = ODComponentTypes::OPERATION_COMPONENT_TYPE;
    protected static string $componentSource = ODComponentTypes::APP_COMPONENT_SOURCE;

    /**
     * @var array|string[]
     */
    protected static array $requiredAttributeArgumentNames = [
        'attribute',
    ];

    protected static array $requiredParametersArgumentNames = [
        'value',
    ];

    /**
     * @var \OpenDialogAi\AttributeEngine\Attributes\Attribute[]
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $parameters;

    public function __construct($attributes = [], $parameters = [])
    {
        $this->attributes = $attributes;
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($attributes): OperationInterface
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function setParameters($parameters): OperationInterface
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasParameter($parameterName): bool
    {
        return isset($this->parameters[$parameterName]);
    }

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        if (!$this->checkRequiredParameters()) {
            return false;
        }

        return $this->performOperation();
    }

    /**
     * @inheritDoc
     */
    final public static function getRequiredAttributeArgumentNames(): array
    {
        return static::$requiredAttributeArgumentNames;
    }

    /**
     * @inheritDoc
     */
    final public static function getRequiredParameterArgumentNames(): array
    {
        return static::$requiredParametersArgumentNames;
    }

    /**
     * @return bool
     */
    protected function checkRequiredParameters(): bool
    {
        $requiredParameters = $this->getRequiredParameterArgumentNames();

        foreach ($requiredParameters as $parameterName) {
            if (!$this->hasParameter($parameterName)) {
                Log::warning(
                    sprintf(
                        "Missing required '%s' parameter for the '%s' operation",
                        $parameterName,
                        self::$componentId
                    )
                );
                return false;
            }
        }

        return true;
    }

    public static function getName(): string
    {
        return static::$componentId;
    }
}
