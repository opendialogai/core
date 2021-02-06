<?php

namespace OpenDialogAi\ContextEngine\Contexts;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;
use OpenDialogAi\ContextEngine\Contracts\Context;

class AbstractContext implements Context
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
