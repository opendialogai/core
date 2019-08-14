<?php


namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class OpeningIntentTest extends TestCase
{
    public function testExpectedAttributes()
    {
        $openingIntent = new OpeningIntent('test', null, null, null, null);

        $openingIntent->addExpectedAttribute('test.attribute1');
        $openingIntent->addExpectedAttribute('attribute2');

        $expectedAttributes = $openingIntent->getExpectedAttributeContexts();
        $this->assertArrayHasKey('attribute1', $expectedAttributes->toArray());
        $this->assertArrayHasKey('attribute2', $expectedAttributes->toArray());


        $this->assertEquals('test', $expectedAttributes->get('attribute1'));
        $this->assertEquals(AbstractAttribute::UNDEFINED_CONTEXT, $expectedAttributes->get('attribute2'));
    }
}
