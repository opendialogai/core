<?php

namespace OpenDialogAi\AttributeEngine\AttributeBag;

use Ds\Map;
use OpenDialogAi\AttributeEngine\HasAttributesTrait;

class AttributeBag implements AttributeBagInterface
{
    use HasAttributesTrait;

    public function __construct()
    {
        $this->attributes = new Map();
    }
}
