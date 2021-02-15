<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;

class IntentTest extends TestCase
{

    /**
     * @group skip
     */
    public function testMatches()
    {
        $intent1 = new Intent('test_intent');
        $intent1->setConfidence(1);

        $intent2 = new Intent('test_intent');
        $intent2->setConfidence(0.5);

        $this->assertTrue($intent1->matches($intent2));
        $this->assertFalse($intent2->matches($intent1));

        $intent2->setId('updated');

        $this->assertFalse($intent1->matches($intent2));
    }

    /**
     * @group skip
     */
    public function testCopyAttributes()
    {
        $intent1 = new Intent('test_intent_1');
        $attribute = new StringAttribute('test_id', 'test_value');
        $intent1->addAttribute($attribute);

        $intent2 = new Intent('test_intent_2');

        $this->assertCount(0, $intent2->getNonCoreAttributes());

        $intent2->copyNonCoreAttributes($intent1);

        $this->assertCount(1, $intent2->getNonCoreAttributes());

        $this->assertEquals($attribute, $intent2->getNonCoreAttributes()->first()->value);
    }
}
