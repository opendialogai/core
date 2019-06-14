<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Message\WebChatMessageFormatter;

class ResponseEngineWebchatMessageFormatterTest extends TestCase
{
    public function testEmptyMessage()
    {
        $markup = '<message disable_text="1"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $this->assertEquals(true, $messages[0]->isEmpty());
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = '<message disable_text="0"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $this->assertEquals(true, $messages[0]->isEmpty());
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);
    }

    public function testTextMessage()
    {
        $markup = '<message disable_text="1"><text-message>hi there</text-message></message>';
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $this->assertEquals('hi there', $messages[0]->getText());
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = <<<EOT
<message disable_text="0">
  <text-message>
    hi there
  </text-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $this->assertEquals('hi there', $messages[0]->getText());
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);
    }

    public function testImageMessage()
    {
        $markup = '<message disable_text="1"><image-message link_new_tab="1"><link>https://www.opendialog.ai</link><src>https://www.opendialog.ai/assets/images/logo.svg</src></image-message></message>';
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];
        $this->assertEquals('https://www.opendialog.ai', $message->getImgLink());
        $this->assertEquals('https://www.opendialog.ai/assets/images/logo.svg', $message->getImgSrc());
        $this->assertEquals(true, $message->getLinkNewTab());
        $this->assertEquals(1, $message->getData()['disable_text']);

        $markup = <<<EOT
<message disable_text="0">
  <image-message link_new_tab="0">
    <link>
      https://www.opendialog.ai
    </link>
    <src>
      https://www.opendialog.ai/assets/images/logo.svg
    </src>
  </image-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];
        $this->assertEquals('https://www.opendialog.ai', $message->getImgLink());
        $this->assertEquals('https://www.opendialog.ai/assets/images/logo.svg', $message->getImgSrc());
        $this->assertEquals(false, $message->getLinkNewTab());
        $this->assertEquals(0, $message->getData()['disable_text']);
    }

    public function testButtonMessage()
    {
        $markup = '<message disable_text="1"><button-message clear_after_interaction="1"><button><text>Yes</text><callback>callback_yes</callback><value>true</value></button><button><text>No</text><callback>callback_no</callback><value>false</value></button></button-message></message>';
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $expectedOutput = [
            [
                'text' => 'Yes',
                'callback_id' => 'callback_yes',
                'value' => 'true',
            ],
            [
                'text' => 'No',
                'callback_id' => 'callback_no',
                'value' => 'false',
            ],
        ];

        $this->assertEquals(true, $message->getData()['clear_after_interaction']);
        $this->assertEquals(true, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getButtonsArray());

        $markup = <<<EOT
<message disable_text="0">
  <button-message clear_after_interaction="0">
    <button>
      <text>
        Yes
      </text>
      <callback>
        callback_yes
      </callback>
      <value>
        true
      </value>
    </button>
    <button>
      <text>
        This is a link
      </text>
      <link new_tab="true">
        https://www.opendialog.ai
      </link>
    </button>
    <button>
      <text>
        No
      </text>
      <callback>
        callback_no
      </callback>
      <value>
        false
      </value>
    </button>
  </button-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $expectedOutput = [
            [
                'text' => 'Yes',
                'callback_id' => 'callback_yes',
                'value' => 'true',
            ],
            [
                'text' => 'This is a link',
                'link' => 'https://www.opendialog.ai',
                'link_new_tab' => true,
            ],
            [
                'text' => 'No',
                'callback_id' => 'callback_no',
                'value' => 'false',
            ],
        ];

        $this->assertEquals(false, $message->getData()['clear_after_interaction']);
        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getButtonsArray());
    }

    public function testRichMessage1()
    {
        $markup = <<<EOT
<message disable_text="0">
  <rich-message>
    <title>Message Title</title>
    <subtitle>This is a subtitle</subtitle>
    <text>Here is a bit of text about this thing</text>
    <button>
      <text>Test 1</text>
      <tab_switch>true</tab_switch>
    </button>
    <button>
      <text>Test 2</text>
      <callback>callback</callback>
      <value>value</value>
    </button>
    <button>
      <text>Test 3</text>
      <link>https://www.opendialog.ai</link>
    </button>
    <button>
      <text>Test 4</text>
      <link new_tab="true">https://www.opendialog.ai</link>
    </button>
    <image>
      <src>https://www.opendialog.ai/assets/images/logo.svg</src>
      <url new_tab="true">https://www.opendialog.ai</url>
    </image>
  </rich-message>
</message>
EOT;
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $expectedOutput = [
            'title' => 'Message Title',
            'subtitle' => 'This is a subtitle',
            'text' => 'Here is a bit of text about this thing',
            'buttons' => [
                [
                    'text' => 'Test 1',
                    'tab_switch' => true,
                ],
                [
                    'text' => 'Test 2',
                    'callback_id' => 'callback',
                    'value' => 'value',
                ],
                [
                    'text' => 'Test 3',
                    'link' => 'https://www.opendialog.ai',
                    'link_new_tab' => false,
                ],
                [
                    'text' => 'Test 4',
                    'link' => 'https://www.opendialog.ai',
                    'link_new_tab' => true,
                ],
            ],
            'image' => [
                'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                'url' => 'https://www.opendialog.ai',
                'link_new_tab' => true,
            ],
        ];

        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertArraySubset($expectedOutput, $message->getData());
    }

    public function testRichMessage2()
    {
        $markup = <<<EOT
<message disable_text="0">
  <rich-message>
    <title>Message Title</title>
    <subtitle>This is a subtitle</subtitle>
    <text>Here is a bit of text about this thing</text>
    <button>
      <text>Test</text>
      <callback>callback</callback>
      <value>value</value>
    </button>
  </rich-message>
</message>
EOT;
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $expectedOutput = [
            'title' => 'Message Title',
            'subtitle' => 'This is a subtitle',
            'text' => 'Here is a bit of text about this thing',
            'buttons' => [
                [
                    'text' => 'Test',
                    'callback_id' => 'callback',
                    'value' => 'value',
                ],
            ],
        ];

        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertArraySubset($expectedOutput, $message->getData());
    }

    public function testRichMessage3()
    {
        $markup = <<<EOT
<message disable_text="0">
  <rich-message>
    <title>Message Title</title>
    <subtitle>This is a subtitle</subtitle>
    <text>Here is a bit of text about this thing</text>
    <image>
      <src>https://www.opendialog.ai/assets/images/logo.svg</src>
      <url new_tab="true">https://www.opendialog.ai</url>
    </image>
  </rich-message>
</message>
EOT;
        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $expectedOutput = [
            'title' => 'Message Title',
            'subtitle' => 'This is a subtitle',
            'text' => 'Here is a bit of text about this thing',
            'image' => [
                'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                'url' => 'https://www.opendialog.ai',
                'link_new_tab' => true,
            ],
        ];

        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertArraySubset($expectedOutput, $message->getData());
    }

    public function testListMessage()
    {
        $markup = <<<EOT
<message disable_text="0">
  <list-message view-type="vertical">
    <item>
      <button-message>
        <text>button message text</text>
        <button>
          <text>Yes</text>
        </button>
      </button-message>
    </item>
    <item>
      <image-message>
        <src>
          https://www.opendialog.ai/assets/images/logo.svg
        </src>
        <link new_tab="true">
          https://www.opendialog.ai
        </link>
      </image-message>
    </item>
    <item>
      <text-message>message-text</text-message>
    </item>
  </list-message>
</message>
EOT;

        $expectedOutput = [
            [
                'text' => 'button message text',
                'disable_text' => false,
                'internal' => false,
                'hidetime' => false,
                'buttons' => [
                    [
                        'text' => 'Yes',
                        'callback_id' => '',
                        'value' => '',
                    ],
                ],
                'clear_after_interaction' => false,
                'message_type' => 'button',
            ],
            [
                'img_src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                'img_link' => 'https://www.opendialog.ai',
                'link_new_tab' => false,
                'disable_text' => false,
                'internal' => false,
                'hidetime' => false,
                'message_type' => 'image',
            ],
            [
                'text' => 'message-text',
                'disable_text' => false,
                'internal' => false,
                'hidetime' => false,
                'message_type' => 'text',
            ],
        ];

        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertEquals('vertical', $data['view_type']);
        $this->assertArraySubset($expectedOutput[0], $data['items'][0]);
        $this->assertArraySubset($expectedOutput[1], $data['items'][1]);
        $this->assertArraySubset($expectedOutput[2], $data['items'][2]);
    }

    public function testFormMessage()
    {
        $markup = <<<EOT
<message disable_text="0">
  <form-message>
    <text>Here is a bit of text about this thing</text>
    <submit_text>This is submit text</submit_text>
    <callback>callback</callback>
    <auto_submit>true</auto_submit>
    <element>
      <element_type>text</element_type>
      <name>first_name</name>
      <display>First name</display>
      <required>false</required>
    </element>
    <element>
      <element_type>text</element_type>
      <name>last_name</name>
      <display>Last name</display>
      <required>true</required>
    </element>
    <element>
      <element_type>select</element_type>
      <name>age</name>
      <display>Age</display>
      <options>
        <option>
          <key>1</key>
          <value>1 year</value>
        </option>
        <option>
          <key>10</key>
          <value>10 year</value>
        </option>
        <option>
          <key>20</key>
          <value>20 year</value>
        </option>
      </options>
    </element>
  </form-message>
</message>
EOT;

        $expectedOutput = [
            'text' => 'Here is a bit of text about this thing',
            'disable_text' => false,
            'callback_id' => 'callback',
            'auto_submit' => true,
            'submit_text' => 'This is submit text',
            'elements' => [
                [
                    'name' => 'first_name',
                    'display' => 'First name',
                    'required' => true,
                    'element_type' => 'text',
                ],
                [
                    'name' => 'last_name',
                    'display' => 'Last name',
                    'required' => true,
                    'element_type' => 'text',
                ],
                [
                    'name' => 'age',
                    'display' => 'Age',
                    'required' => false,
                    'element_type' => 'select',
                    'options' => [
                        '1' => '1 year',
                        '10' => '10 year',
                        '20' => '20 year',
                    ],
                ],
            ],
        ];

        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertArraySubset($expectedOutput, $message->getData());
    }

    public function testLongTextMessage()
    {
        $markup = <<<EOT
<message disable_text="0">
  <long-text-message>
    <submit_text>This is submit text</submit_text>
    <callback>callback</callback>
    <initial_text>Initial text</initial_text>
    <placeholder>Enter text</placeholder>
    <confirmation_text>Thank you</confirmation_text>
    <character_limit>200</character_limit>
  </long-text-message>
</message>
EOT;

        $expectedOutput = [
            'disable_text' => false,
            'character_limit' => '200',
            'submit_text' => 'This is submit text',
            'callback_id' => 'callback',
            'initial_text' => 'Initial text',
            'placeholder' => 'Enter text',
            'confirmation_text' => 'Thank you',
        ];

        $formatter = new WebChatMessageFormatter;
        $messages = $formatter->getMessages($markup);
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertArraySubset($expectedOutput, $message->getData());
    }
}
