<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;

class MessageMarkUpGeneratorTest extends TestCase
{
    public function testButtonMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator(true, true);
        $buttons = [
            [
                'text' => 'Button Text',
                'value' => 'Value',
                'callback' => 'callback'
            ]
        ];
        $generator->addButtonMessage('test button', $buttons);

        $markUp = ($generator->getMarkUp());
        $this->assertRegexp('/<message disable_text="true" hide_avatar="true">/', $markUp);
        $this->assertRegexp('/<button-message>/', $markUp);
        $this->assertRegexp('/<button>/', $markUp);
        $this->assertRegexp('/<text>Button Text<\/text>/', $markUp);
        $this->assertRegexp('/<value>Value<\/value>/', $markUp);
        $this->assertRegexp('/<callback>callback<\/callback>/', $markUp);
    }

    public function testTextWithLinkMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessageWithLink('This is an example', 'This is a link', 'http://www.example.com');
        $markUp = ($generator->getMarkUp());
        $this->assertRegexp('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertRegexp('/<text-message>/', $markUp);
        $this->assertRegexp('/<link>/', $markUp);
        $this->assertRegexp('/<text>This is a link<\/text>/', $markUp);
        $this->assertRegexp('/<url>http:\/\/www.example.com<\/url>/', $markUp);
        $this->assertRegexp('/<open-new-tab>true<\/open-new-tab>/', $markUp);
    }

    public function testMetaMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addMetaMessage([
            'myName' => 'myValue'
        ]);
        $markUp = ($generator->getMarkUp());
        $this->assertRegexp('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertRegexp('/<meta-message>/', $markUp);
        $this->assertRegexp('/<data name="myName">myValue<\/data>/', $markUp);
    }

    public function testCtaMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addCtaMessage('My CTA text');
        $markUp = ($generator->getMarkUp());
        $this->assertRegexp('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertRegexp('/<cta-message>/', $markUp);
        $this->assertRegexp('/My CTA text/', $markUp);
    }
}
