<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;
use OpenDialogAi\ContextEngine\Contracts\Context;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;

abstract class AbstractContext implements Context
{
    use HasAttributesTrait;

    private $id;

    private ?ContextDataClient $dataClient;

    protected $dataClientAttributes = [];

    public function __construct($id, ?ContextDataClient $dataClient = null)
    {
        $this->id = $id;
        $this->attributes = new Map();
        $this->dataClient = $dataClient;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function persist(): bool
    {
        return true;
    }
}
