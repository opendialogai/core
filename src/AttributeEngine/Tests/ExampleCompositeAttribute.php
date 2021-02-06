<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\FloatAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\FloatAttributeValue;
use OpenDialogAi\AttributeEngine\AttributeValues\IntAttributeValue;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;

class ExampleCompositeAttribute extends BasicCompositeAttribute
{
    public static function generate(): ExampleCompositeAttribute
    {
        $name = new StringAttribute('name', new StringAttributeValue('Gigi'));
        $user_id = new IntAttribute('user_id', new IntAttributeValue('123123'));
        $score = new FloatAttribute('score', new FloatAttributeValue(10.34));

        $compositeAttribute = new ExampleCompositeAttribute();
        $compositeAttribute->addAttribute($name);
        $compositeAttribute->addAttribute($user_id);
        $compositeAttribute->addAttribute($score);
    }
}
