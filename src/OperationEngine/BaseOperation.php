<?php

namespace OpenDialogAi\OperationEngine;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\Core\Traits\HasName;

abstract class BaseOperation extends BaseOpenDialogComponent implements OperationInterface
{
    use HasName;

    protected static string $componentType = BaseOpenDialogComponent::OPERATION_COMPONENT_TYPE;
    protected static string $componentSource = BaseOpenDialogComponent::APP_COMPONENT_SOURCE;

    /**
     * @var \OpenDialogAi\AttributeEngine\Attributes\AttributeInterface[]
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $parameters;

    protected static $name = 'base';

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
    public function hasParameter($parameterName) : bool
    {
        return isset($this->parameters[$parameterName]);
    }

    /**
     * @inheritDoc
     */
    public function execute() : bool
    {
        if (!$this->checkRequiredParameters()) {
            return false;
        }

        return $this->performOperation();
    }

    /**
     * @return bool
     */
    protected function checkRequiredParameters() : bool
    {
        $parameters = $this->getAllowedParameters();
        $requiredParameters = (isset($parameters['required'])) ? $parameters['required'] : [];

        foreach ($requiredParameters as $parameterName) {
            if (!$this->hasParameter($parameterName)) {
                Log::warning(
                    sprintf(
                        "Missing required '%s' parameter for the '%s' operation",
                        $parameterName,
                        self::$name
                    )
                );
                return false;
            }
        }

        return true;
    }
}
