<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;
use OpenDialogAi\ContextEngine\Contracts\Context;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;
use OpenDialogAi\Core\Components\ODComponentTypes;

abstract class AbstractContext implements Context, OpenDialogComponent
{
    use HasAttributesTrait;
    use ODComponent;

    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;
    protected static string $componentType = ODComponentTypes::CONTEXT_COMPONENT_TYPE;

    protected static bool $attributesAreReadOnly = false;

    protected ContextDataClient $dataClient;

    protected $dataClientAttributes = [];

    public function __construct(ContextDataClient $dataClient = null)
    {
        $this->attributes = new Map();

        if (!is_null($dataClient)) {
            $this->dataClient = $dataClient;
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return static::getComponentId();
    }

    public function persist(): bool
    {
        return true;
    }

    public static function attributesAreReadOnly(): bool
    {
        return static::$attributesAreReadOnly;
    }
}
