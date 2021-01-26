<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\TimestampAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class TimestampAttributeTest extends TestCase
{
    public function testToString()
    {
        $timestamp = "1578312373";
        $attribute = new TimestampAttribute('test', $timestamp);

        $this->assertEquals("2020-01-06T12:06:13+00:00", $attribute->toString());
    }
}
