<?php

namespace OpenDialogAi\Core\Attribute\AttributeBag;

use Ds\Map;
use OpenDialogAi\Core\Attribute\HasAttributesTrait;

class AttributeBag implements AttributeBagInterface
{
    use HasAttributesTrait;

    public function __construct()
    {
        $this->attributes = new Map();
    }
}
