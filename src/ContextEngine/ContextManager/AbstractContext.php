<?php

namespace OpenDialogAi\ContextEngine\ContextManager;

use Ds\Map;
use OpenDialogAi\AttributeEngine\HasAttributesTrait;

class AbstractContext implements ContextInterface
{
    use HasAttributesTrait;

    private $id;

    public function __construct($id)
    {
        $this->id = $id;
        $this->attributes = new Map();
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
