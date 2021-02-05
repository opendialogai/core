<?php

namespace OpenDialogAi\AttributeEngine\AttributeBag;

use Ds\Map;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;

class BasicAttributeBag implements AttributeBag
{
    use HasAttributesTrait;

    public function __construct()
    {
        $this->attributes = new Map();
    }
}
