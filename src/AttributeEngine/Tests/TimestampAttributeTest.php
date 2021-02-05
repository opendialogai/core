<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\TimestampAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\TimestampAttributeValue;
use OpenDialogAi\Core\Tests\TestCase;

class TimestampAttributeTest extends \Orchestra\Testbench\TestCase
{
    public function testToString()
    {
        $timestamp = new TimestampAttributeValue("1578312373");
        $attribute = new TimestampAttribute('test', null, $timestamp);
        $this->assertEquals("2020-01-06T12:06:13+00:00", $attribute->toString());


        $attribute = new TimestampAttribute('test', "1578312373");
        $this->assertEquals("2020-01-06T12:06:13+00:00", $attribute->toString());
    }
}
