<?php

namespace OpenDialogAi\ActionEngine\Tests;

use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ActionResultTest extends TestCase
{
    public function testFailedActionResult()
    {
        $result = ActionResult::createFailedActionResult();

        $this->assertFalse($result->isSuccessful());
    }

    public function testSuccessfulResult()
    {
        $result = ActionResult::createSuccessfulActionResultWithAttributes([]);

        $this->assertTrue($result->isSuccessful());
    }

    public function testSuccessfulResultWithAttributes()
    {
        $result = ActionResult::createSuccessfulActionResultWithAttributes([new StringAttribute('test', 'value')]);

        $this->assertTrue($result->isSuccessful());
        $this->assertCount(1, $result->getResultAttributes()->getAttributes());
    }
}
