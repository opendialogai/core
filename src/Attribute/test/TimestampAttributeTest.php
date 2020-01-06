<?php

namespace OpenDialogAi\Core\Attribute\test;

use Carbon\Carbon;
use OpenDialogAi\Core\Attribute\TimestampAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class TimestampAttributeTest extends TestCase
{
    public function testToString()
    {
        $timestamp = "1578312373";
        $attribute = new TimestampAttribute('test', $timestamp);

        $this->assertEquals(Carbon::createFromTimestamp($timestamp)->toIso8601String(), $attribute->toString());
    }
}
