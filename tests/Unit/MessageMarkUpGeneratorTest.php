<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;

class MessageMarkUpGeneratorTest extends TestCase
{
    public function testButtonMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $buttons = [
            [
                'text' => 'Button Text',
                'value' => 'Value',
                'callback' => 'callback'
            ]
        ];
        $generator->addButtonMessage('test button', $buttons);

        $markUp = ($generator->getMarkUp());
        $this->assertRegexp('/<message>/', $markUp);
        $this->assertRegexp('/<button-message>/', $markUp);
        $this->assertRegexp('/<button>/', $markUp);
        $this->assertRegexp('/<text>Button Text<\/text>/', $markUp);
        $this->assertRegexp('/<value>Value<\/value>/', $markUp);
        $this->assertRegexp('/<callback>callback<\/callback>/', $markUp);
    }
}

