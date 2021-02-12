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

        $markUp = $generator->getMarkUp();
        $this->assertMatchesRegularExpression('/<message disable_text="true" hide_avatar="true">/', $markUp);
        $this->assertMatchesRegularExpression('/<button-message>/', $markUp);
        $this->assertMatchesRegularExpression('/<button>/', $markUp);
        $this->assertMatchesRegularExpression('/<text>Button Text<\/text>/', $markUp);
        $this->assertMatchesRegularExpression('/<value>Value<\/value>/', $markUp);
        $this->assertMatchesRegularExpression('/<callback>callback<\/callback>/', $markUp);
    }

    public function testTextWithLinkMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessageWithLink('This is an example', 'This is a link', 'http://www.example.com');
        $markUp = $generator->getMarkUp();
        $this->assertMatchesRegularExpression('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertMatchesRegularExpression('/<text-message>/', $markUp);
        $this->assertMatchesRegularExpression('/<link>/', $markUp);
        $this->assertMatchesRegularExpression('/<text>This is a link<\/text>/', $markUp);
        $this->assertMatchesRegularExpression('/<url>http:\/\/www.example.com<\/url>/', $markUp);
        $this->assertMatchesRegularExpression('/<open-new-tab>true<\/open-new-tab>/', $markUp);
    }

    public function testMetaMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addMetaMessage([
            'myName' => 'myValue'
        ]);
        $markUp = $generator->getMarkUp();
        $this->assertMatchesRegularExpression('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertMatchesRegularExpression('/<meta-message>/', $markUp);
        $this->assertMatchesRegularExpression('/<data name="myName">myValue<\/data>/', $markUp);
    }

    public function testCtaMarkUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addCtaMessage('My CTA text');
        $markUp = $generator->getMarkUp();
        $this->assertMatchesRegularExpression('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertMatchesRegularExpression('/<cta-message>/', $markUp);
        $this->assertMatchesRegularExpression('/My CTA text/', $markUp);
    }

    public function testAutoCompleteMarkUpGenerator()
    {
        $endpointParams = ['country' => 'gb', 'query' => 'value'];

        $generator = new MessageMarkUpGenerator();
        $generator->addAutoCompleteMessage(
            'This is the title',
            '/api/v3/endpoint-url',
            'query',
            'callback',
            'Submit',
            'placeholder...',
            'Product',
            $endpointParams
        );
        $markUp = $generator->getMarkUp();
        $this->assertMatchesRegularExpression('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertMatchesRegularExpression('/<autocomplete-message>/', $markUp);
        $this->assertMatchesRegularExpression('/This is the title/', $markUp);
        $this->assertMatchesRegularExpression('/<url>\/api\/v3\/endpoint-url<\/url>/', $markUp);
        $this->assertMatchesRegularExpression('/<attribute_name>Product<\/attribute_name>/', $markUp);
    }

    public function testDatePickerUpGenerator()
    {
        $generator = new MessageMarkUpGenerator();
        $generator->addDatePickerMessage(
            'Message text',
            'callback',
            'Submit',
            'Product',
            'today',
            '20200101',
            false,
            false,
            true
        );
        $markUp = $generator->getMarkUp();

        $this->assertMatchesRegularExpression('/<message disable_text="false" hide_avatar="false">/', $markUp);
        $this->assertMatchesRegularExpression('/<date-picker-message>/', $markUp);
        $this->assertMatchesRegularExpression('/<text>Message text<\/text>/', $markUp);
        $this->assertMatchesRegularExpression('/<callback>callback<\/callback>/', $markUp);
        $this->assertMatchesRegularExpression('/<submit_text>Submit<\/submit_text>/', $markUp);
        $this->assertMatchesRegularExpression('/<month_required><\/month_required>/', $markUp);
        $this->assertMatchesRegularExpression('/<year_required>1<\/year_required>/', $markUp);
        $this->assertMatchesRegularExpression('/<max_date>today<\/max_date>/', $markUp);
        $this->assertMatchesRegularExpression('/<min_date>20200101<\/min_date>/', $markUp);
        $this->assertMatchesRegularExpression('/<attribute_name>Product<\/attribute_name>/', $markUp);
    }
}
