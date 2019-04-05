<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;

class IntentTest extends TestCase
{
    public function testCoreAttributes()
    {
        $intent = Intent::createIntentWithConfidence('test', 1);
        $attribute = new StringAttribute('test', 'test');
        $intent->addAttribute($attribute);

        $this->assertCount(1, $intent->getNonCoreAttributes());
        $this->assertEquals($attribute, $intent->getNonCoreAttributes()->first()->value);
    }
}
