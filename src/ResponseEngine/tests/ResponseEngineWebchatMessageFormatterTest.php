<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\Formatters\Webchat\WebChatMessageFormatter;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Rules\MessageXML;

class ResponseEngineWebchatMessageFormatterTest extends TestCase
{
    use ArraySubsetAsserts;

    public function testEmptyMessage()
    {
        $markup = '<message disable_text="1"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(true, $messages[0]->isEmpty());
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = '<message disable_text="0"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(true, $messages[0]->isEmpty());
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);
    }

    public function testDisableTextProperty()
    {
        $markup = '<message disable_text="1"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = '<message disable_text="true"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = '<message disable_text="0"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);

        $markup = '<message disable_text="false"><empty-message></empty-message></message>';
        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);
    }

    public function testTextMessage()
    {
        $markup = '<message disable_text="1"><text-message>hi there</text-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('hi there', $messages[0]->getText());
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = <<<EOT
<message disable_text="0">
  <text-message>
    hi there
  </text-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('hi there', $messages[0]->getText());
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);
    }

    public function testTextMessageWithLink()
    {
        $markup = <<<EOT
<message disable_text="0">
  <text-message>
    hi there
    <link><url>http://www.opendialog.ai</url><text>Link 1</text></link>
    <link new_tab="true"><url>http://www.opendialog.ai</url><text>Link 2</text></link>
    test
    <link new_tab="0"><url>http://www.opendialog.ai</url><text>Link 3</text></link>
  </text-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('hi there <a class="linkified" target="_parent" href="http://www.opendialog.ai">Link 1</a> <a class="linkified" target="_blank" href="http://www.opendialog.ai">Link 2</a> test <a class="linkified" target="_parent" href="http://www.opendialog.ai">Link 3</a>', $messages[0]->getText());
    }

    public function testHandToHumanMessage()
    {
        $markup = '<message disable_text="1"><hand-to-human-message><data name="history">{message_history.all}</data><data name="email">{user.email}</data></hand-to-human-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];
        $this->assertEquals(1, $message->getData()['disable_text']);

        $elements = $message->getElements();
        $this->assertEquals('{message_history.all}', $elements['history']);
        $this->assertEquals('{user.email}', $elements['email']);
    }

    public function testImageMessage()
    {
        // phpcs:ignore
        $markup = '<message disable_text="1"><image-message link_new_tab="1"><link>https://www.opendialog.ai</link><src>https://www.opendialog.ai/assets/images/logo.svg</src></image-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
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

        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];
        $this->assertEquals('https://www.opendialog.ai', $message->getImgLink());
        $this->assertEquals('https://www.opendialog.ai/assets/images/logo.svg', $message->getImgSrc());
        $this->assertEquals(false, $message->getLinkNewTab());
        $this->assertEquals(0, $message->getData()['disable_text']);
    }

    public function testButtonMessage()
    {
        // phpcs:ignore
        $markup = '<message disable_text="1"><button-message clear_after_interaction="1"><text>test</text><button type="yes-button"><text>Yes</text><callback>callback_yes</callback><value>true</value></button><button type="no-button"><text>No</text><callback>callback_no</callback><value>false</value></button><button><text>Hidden</text><callback>hidden</callback><display>false</display></button></button-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            [
                'text' => 'Yes',
                'callback_id' => 'callback_yes',
                'value' => 'true',
                'display' => true,
                'type' => 'yes-button',
            ],
            [
                'text' => 'No',
                'callback_id' => 'callback_no',
                'value' => 'false',
                'display' => true,
                'type' => 'no-button',
            ],
            [
                'text' => 'Hidden',
                'callback_id' => 'hidden',
                'value' => '',
                'display' => false,
                'type' => ''
            ],
        ];

        $this->assertEquals(true, $message->getData()['clear_after_interaction']);
        $this->assertEquals(true, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getButtonsArray());

        $markup = <<<EOT
<message disable_text="0">
  <button-message clear_after_interaction="0">
    <text>test</text>
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
    <button>
      <text>
        Click to call
      </text>
      <click_to_call>
        12312412
      </click_to_call>
    </button>
  </button-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            [
                'text' => 'Yes',
                'callback_id' => 'callback_yes',
                'value' => 'true',
                'display' => true,
                'type' => '',
            ],
            [
                'text' => 'This is a link',
                'link' => 'https://www.opendialog.ai',
                'link_new_tab' => true,
                'display' => true,
                'type' => '',
            ],
            [
                'text' => 'No',
                'callback_id' => 'callback_no',
                'value' => 'false',
                'display' => true,
                'type' => '',
            ],
            [
                'text' => 'Click to call',
                'phone_number' => '12312412',
                'display' => true,
                'type' => '',
            ],
        ];

        $this->assertEquals(false, $message->getData()['clear_after_interaction']);
        $this->assertEquals(false, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getButtonsArray());
    }

    public function testButtonMessageWithUnderline()
    {
        $markup = <<<EOT
<message disable_text="0">
  <button-message clear_after_interaction="0">
    <text>test</text>
    <button>
      <text>
        This is an <u>underline</u> text with <u>underline</u>
      </text>
      <callback>
        callback_yes
      </callback>
      <value>
        true
      </value>
    </button>
  </button-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            [
                'text' => 'This is an <u>underline</u> text with <u>underline</u>',
                'callback_id' => 'callback_yes',
                'value' => 'true',
                'display' => true,
                'type' => '',
            ],
        ];

        $this->assertEquals($expectedOutput, $message->getButtonsArray());
    }

    public function testButtonMessageWithBoldAndItalic()
    {
        $markup = <<<EOT
<message disable_text="0">
  <button-message clear_after_interaction="0">
    <text>test</text>
    <button>
      <text>
        This is an <b>bold</b> text with <i>italic</i>
      </text>
      <callback>
        callback_yes
      </callback>
      <value>
        true
      </value>
    </button>
  </button-message>
</message>
EOT;

        $messageXML = new MessageXML();
        $this->assertTrue($messageXML->passes(null, $markup), $messageXML->message());

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            [
                'text' => 'This is an <strong>bold</strong> text with <em>italic</em>',
                'callback_id' => 'callback_yes',
                'value' => 'true',
                'display' => true,
                'type' => '',
            ],
        ];

        $this->assertEquals($expectedOutput, $message->getButtonsArray());
    }

    public function testRichMessage1()
    {
        $buttons = [
            [
                'text' => 'Test 1',
                'tab_switch' => true,
            ],
            [
                'text' => 'Test 2',
                'callback' => 'callback',
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
            [
                'text' => 'Test 5',
                'download' => true,
            ],
        ];

        $image = [
            'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
            'url' => 'https://www.opendialog.ai',
            'new_tab' => true
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addRichMessage('Message Title', 'This is a subtitle', 'Here is a bit of text about this thing', '', '', 'http://www.example.com', $buttons, $image);

        $markup = $messageMarkUp->getMarkUp();

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            'title' => 'Message Title',
            'subtitle' => 'This is a subtitle',
            'text' => 'Here is a bit of text about this thing',
            'link' => 'http://www.example.com',
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
                [
                    'text' => 'Test 5',
                    'download' => true,
                ],
            ],
            'image' => [
                'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                'url' => 'https://www.opendialog.ai',
                'link_new_tab' => true,
            ],
        ];

        $this->assertEquals(false, $message->getData()['disable_text']);
        self::assertArraySubset($expectedOutput, $message->getData(), true);
    }

    public function testRichMessage2()
    {
        $buttons = [
            [
                'text' => 'Test',
                'callback' => 'callback',
                'value' => 'value'
            ]
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addRichMessage('Message Title', 'This is a subtitle', 'Here is a bit of text about this thing', 'callback_yes', 'value', '', $buttons);

        $markup = $messageMarkUp->getMarkUp();

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            'title' => 'Message Title',
            'subtitle' => 'This is a subtitle',
            'text' => 'Here is a bit of text about this thing',
            'callback' => 'callback_yes',
            'callback_value' => 'value',
            'buttons' => [
                [
                    'text' => 'Test',
                    'callback_id' => 'callback',
                    'value' => 'value',
                ],
            ],
        ];

        $this->assertEquals(false, $message->getData()['disable_text']);

        self::assertArraySubset($expectedOutput, $message->getData(), true);
    }

    public function testRichMessage3()
    {
        $image = [
            'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
            'url' => 'https://www.opendialog.ai',
            'new_tab' => true
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addRichMessage('Message Title', 'This is a subtitle', 'Here is a bit of text about this thing', '', '', '', [], $image);

        $markup = $messageMarkUp->getMarkUp();

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
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
        self::assertArraySubset($expectedOutput, $message->getData(), true);
    }

    public function testFullPageRichMessage1()
    {
        $buttons = [
            [
                'text' => 'Test 1',
                'tab_switch' => true,
            ],
            [
                'text' => 'Test 2',
                'callback' => 'callback',
                'value' => 'value',
                'display' => false,
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
            [
                'text' => 'Test 5',
                'download' => true,
            ],
        ];

        $image = [
            'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
            'url' => 'https://www.opendialog.ai',
            'new_tab' => true
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addFullPageRichMessage('Message Title', 'This is a subtitle', 'Here is a bit of text about this thing', $buttons, $image);

        $markup = $messageMarkUp->getMarkUp();

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $expectedOutput = [
            'title' => 'Message Title',
            'subtitle' => 'This is a subtitle',
            'text' => 'Here is a bit of text about this thing',
            'buttons' => [
                [
                    'text' => 'Test 1',
                    'tab_switch' => true,
                    'display' => true,
                ],
                [
                    'text' => 'Test 2',
                    'callback_id' => 'callback',
                    'value' => 'value',
                    'display' => false,
                ],
                [
                    'text' => 'Test 3',
                    'link' => 'https://www.opendialog.ai',
                    'link_new_tab' => false,
                    'display' => true,
                ],
                [
                    'text' => 'Test 4',
                    'link' => 'https://www.opendialog.ai',
                    'link_new_tab' => true,
                    'display' => true,
                ],
                [
                    'text' => 'Test 5',
                    'download' => true,
                    'display' => true,
                ],
            ],
            'image' => [
                'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                'url' => 'https://www.opendialog.ai',
                'link_new_tab' => true,
            ],
        ];

        $this->assertEquals(false, $message->getData()['disable_text']);
        self::assertArraySubset($expectedOutput, $message->getData(), true);
    }

    public function testFullPageRichMessage2()
    {
        $buttons = [
            [
                'text' => 'Test',
                'callback' => 'callback',
                'value' => 'value'
            ]
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addFullPageRichMessage('Message Title', 'This is a subtitle', 'Here is a bit of text about this thing', $buttons);

        $markup = $messageMarkUp->getMarkUp();

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
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

        self::assertArraySubset($expectedOutput, $message->getData(), true);
    }

    public function testFullPageRichMessage3()
    {
        $image = [
            'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
            'url' => 'https://www.opendialog.ai',
            'new_tab' => true
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addFullPageRichMessage('Message Title', 'This is a subtitle', 'Here is a bit of text about this thing', [], $image);

        $markup = $messageMarkUp->getMarkUp();

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
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
        self::assertArraySubset($expectedOutput, $message->getData(), true);
    }

    public function testListMessage()
    {
        $messages = [
            [
                'button' => [
                    'text' => 'button message text',
                    'external' => false,
                    'buttons' => [
                        [
                            'text' => 'Yes',
                            'callback' => 'callback',
                            'value' => ''
                        ]
                    ]
                ]
            ],
            [
                'image' => [
                    'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                    'link' => 'https://www.opendialog.ai',
                    'new_tab' => true
                ]
            ],
            [
                'text' => [
                    'text' => 'message-text'
                ]
            ],
            [
                'rich' => [
                    'title' => 'rich message title',
                    'subtitle' => 'rich message subtitle',
                    'text' => 'rich message text',
                    'callback' => 'callback_yes',
                    'callback_value' => 'value',
                    'link' => '',
                    'buttons' => [
                        [
                            'text' => 'Yes',
                            'callback' => 'callback',
                            'value' => ''
                        ]
                    ],
                    'image' => [
                        'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                        'url' => 'https://www.opendialog.ai',
                        'new_tab' => true
                    ]
                ]
            ],
            [
                'rich' => [
                    'title' => 'rich message title 2',
                    'subtitle' => 'rich message subtitle 2',
                    'text' => 'rich message text 2',
                    'callback' => '',
                    'callback_value' => '',
                    'link' => 'http://www.example.com',
                ]
            ],
        ];

        $messageMarkUp = new MessageMarkUpGenerator(true);
        $messageMarkUp->addListMessage('vertical', 'Test title', $messages);

        $markup = $messageMarkUp->getMarkUp();

        $expectedOutput = [
            [
                'text' => 'button message text',
                'disable_text' => false,
                'internal' => false,
                'hidetime' => false,
                'buttons' => [
                    [
                        'text' => 'Yes',
                        'callback_id' => 'callback',
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
            [
                'title' => 'rich message title',
                'subtitle' => 'rich message subtitle',
                'text' => 'rich message text',
                'callback' => 'callback_yes',
                'callback_value' => 'value',
                'buttons' => [
                    [
                        'text' => 'Yes',
                        'callback_id' => 'callback',
                        'value' => '',
                    ],
                ],
                'image' => [
                    'src' => 'https://www.opendialog.ai/assets/images/logo.svg',
                    'url' => 'https://www.opendialog.ai',
                    'new_tab' => true,
                ],
            ],
            [
                'title' => 'rich message title 2',
                'subtitle' => 'rich message subtitle 2',
                'text' => 'rich message text 2',
                'callback' => '',
                'callback_value' => '',
                'link' => 'http://www.example.com',
            ],
        ];

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(true, $data['disable_text']);
        $this->assertEquals('vertical', $data['view_type']);
        $this->assertEquals('Test title', $data['title']);
        self::assertArraySubset($expectedOutput[0], $data['items'][0]);
        self::assertArraySubset($expectedOutput[1], $data['items'][1]);
        self::assertArraySubset($expectedOutput[2], $data['items'][2]);
    }

    public function testFormMessage()
    {
        $elements = [
            [
                'element_type' => 'text',
                'name' => 'first_name',
                'display' => 'First name',
                'required' => false,
                'default_value' => 'value',
            ],
            [
                'element_type' => 'text',
                'name' => 'last_name',
                'display' => 'Last name',
                'required' => true,
            ],
            [
                'element_type' => 'email',
                'name' => 'email',
                'display' => 'Email',
                'required' => true,
            ],
            [
                'element_type' => 'select',
                'name' => 'age',
                'display' => 'Age',
                'required' => false,
                'default_value' => '10',
                'options' => [
                    [
                        'key' => '1',
                        'value' => '1 year',
                    ],
                    [
                        'key' => '10',
                        'value' => '10 year',
                    ],
                    [
                        'key' => '20',
                        'value' => '20 year',
                    ],
                ],
            ],
            [
                'element_type' => 'radio',
                'name' => 'gender',
                'display' => 'Gender',
                'required' => false,
                'options' => [
                    [
                        'key' => 'male',
                        'value' => 'Male',
                    ],
                    [
                        'key' => 'female',
                        'value' => 'Female',
                    ],
                ],
            ],
            [
                'element_type' => 'auto_complete_select',
                'name' => 'year',
                'display' => 'Year',
                'required' => false,
                'options' => [
                    [
                        'key' => '1',
                        'value' => '2019',
                    ],
                    [
                        'key' => '2',
                        'value' => '2020',
                    ],
                    [
                        'key' => '3',
                        'value' => '2021',
                    ],
                ],
            ],
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addFormMessage('Here is a bit of text about this thing', 'This is submit text', 'callback', true, $elements, 'cancel', 'cancel_callback');

        $markup = $messageMarkUp->getMarkUp();

        $expectedOutput = [
            'text' => 'Here is a bit of text about this thing',
            'disable_text' => false,
            'callback_id' => 'callback',
            'auto_submit' => true,
            'submit_text' => 'This is submit text',
            'cancel_text' => 'cancel',
            'cancel_callback' => 'cancel_callback',
            'elements' => [
                [
                    'name' => 'first_name',
                    'display' => 'First name',
                    'required' => false,
                    'element_type' => 'text',
                    'default_value' => 'value',
                ],
                [
                    'name' => 'last_name',
                    'display' => 'Last name',
                    'required' => true,
                    'element_type' => 'text',
                ],
                [
                    'name' => 'email',
                    'display' => 'Email',
                    'required' => true,
                    'element_type' => 'email',
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
                    'default_value' => '10',
                ],
                [
                    'name' => 'gender',
                    'display' => 'Gender',
                    'required' => false,
                    'element_type' => 'radio',
                    'options' => [
                        'male' => 'Male',
                        'female' => 'Female',
                    ],
                ],
                [
                    'name' => 'year',
                    'display' => 'Year',
                    'required' => false,
                    'element_type' => 'auto-select',
                    'options' => [
                        [
                            'key' => 1,
                            'value' => '2019',
                        ],
                        [
                            'key' => 2,
                            'value' => '2020',
                        ],
                        [
                            'key' => 3,
                            'value' => '2021',
                        ],
                    ],
                ],
            ],
        ];

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(false, $data['disable_text']);
        self::assertArraySubset($expectedOutput, $data, true);
    }

    public function testFullPageFormMessage()
    {
        $elements = [
            [
                'element_type' => 'text',
                'name' => 'first_name',
                'display' => 'First name',
                'required' => false,
                'default_value' => 'value',
            ],
            [
                'element_type' => 'text',
                'name' => 'last_name',
                'display' => 'Last name',
                'required' => true
            ],
            [
                'element_type' => 'select',
                'name' => 'age',
                'display' => 'Age',
                'required' => false,
                'options' => [
                    [
                        'key' => '1',
                        'value' => '1 year'
                    ],
                    [
                        'key' => '10',
                        'value' => '10 year'
                    ],
                    [
                        'key' => '20',
                        'value' => '20 year'
                    ]
                ],
                'default_value' => '10',
            ],
            [
                'element_type' => 'radio',
                'name' => 'gender',
                'display' => 'Gender',
                'required' => false,
                'options' => [
                    [
                        'key' => 'male',
                        'value' => 'Male',
                    ],
                    [
                        'key' => 'female',
                        'value' => 'Female',
                    ],
                ],
            ],
            [
                'element_type' => 'auto_complete_select',
                'name' => 'year',
                'display' => 'Year',
                'required' => false,
                'options' => [
                    [
                        'key' => '1',
                        'value' => '2019'
                    ],
                    [
                        'key' => '2',
                        'value' => '2020'
                    ],
                    [
                        'key' => '3',
                        'value' => '2021'
                    ]
                ]
            ]
        ];

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addFullPageFormMessage('Here is a bit of text about this thing', 'This is submit text', 'callback', true, $elements, 'cancel', 'cancel_callback');

        $markup = $messageMarkUp->getMarkUp();

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
                    'required' => false,
                    'element_type' => 'text',
                    'default_value' => 'value',
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
                    'default_value' => '10',
                ],
                [
                    'name' => 'gender',
                    'display' => 'Gender',
                    'required' => false,
                    'element_type' => 'radio',
                    'options' => [
                        'male' => 'Male',
                        'female' => 'Female',
                    ],
                ],
                [
                    'name' => 'year',
                    'display' => 'Year',
                    'required' => false,
                    'element_type' => 'auto-select',
                    'options' => [
                        [
                            'key' => 1,
                            'value' => '2019',
                        ],
                        [
                            'key' => 2,
                            'value' => '2020',
                        ],
                        [
                            'key' => 3,
                            'value' => '2021',
                        ],
                    ],
                ],
            ],
        ];

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(false, $data['disable_text']);
        self::assertArraySubset($expectedOutput, $data, true);
    }

    public function testLongTextMessage()
    {
        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addLongTextMessage('This is submit text', 'callback', 'Initial text', 'Enter text', 'Thank you', '200');

        $markup = $messageMarkUp->getMarkUp();

        $expectedOutput = [
            'disable_text' => false,
            'character_limit' => '200',
            'submit_text' => 'This is submit text',
            'callback_id' => 'callback',
            'initial_text' => 'Initial text',
            'placeholder' => 'Enter text',
            'confirmation_text' => 'Thank you',
        ];

        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $message = $messages[0];

        $data = $message->getData();

        $this->assertEquals(false, $data['disable_text']);
        self::assertArraySubset($expectedOutput, $data, true);
    }

    public function testCtaMessage()
    {
        $markup = '<message disable_text="1"><cta-message>hi there.</cta-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('hi there.', $messages[0]->getText());
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $markup = <<<EOT
<message disable_text="0">
  <cta-message>hi there.</cta-message>
</message>
EOT;

        $formatter = new WebChatMessageFormatter();
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('hi there.', $messages[0]->getText());
        $this->assertEquals(0, $messages[0]->getData()['disable_text']);
    }

    public function testMetaMessage()
    {
        $markup = '<message disable_text="1"><meta-message><data name="myData">myValue</data></meta-message></message>';
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals(1, $messages[0]->getData()['disable_text']);

        $elements = $messages[0]->getElements();
        $this->assertEquals('myValue', $elements['myData']);
    }

    public function testAutocompleteMessage()
    {
        /** @lang XML */
        $markup = <<<EOT
<message disable_text="1">
    <autocomplete-message>
        <title>Title</title>
        <submit_text>Submit</submit_text>
        <callback>Callback</callback>
        <placeholder>placeholder...</placeholder>
        <attribute_name>Product</attribute_name>
        <options-endpoint>
            <url>/api/to-hit</url>
            <params>
                <param name="country" value="uk" />
                <param name="language" value="en" />
            </params>
            <query-param-name>name</query-param-name>
        </options-endpoint>
    </autocomplete-message>
</message>
EOT;
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('Title', $messages[0]->getData()['title']);
        $this->assertEquals('/api/to-hit', $messages[0]->getData()['endpoint_url']);
        $this->assertEquals('name', $messages[0]->getData()['query_param_name']);
        $this->assertEquals('placeholder...', $messages[0]->getData()['placeholder']);
        $this->assertEquals('Product', $messages[0]->getData()['attribute_name']);

        $endpointParams = $messages[0]->getData()['endpoint_params'];
        $this->assertEquals('country', $endpointParams[0]['name']);
        $this->assertEquals('uk', $endpointParams[0]['value']);
        $this->assertEquals('language', $endpointParams[1]['name']);
        $this->assertEquals('en', $endpointParams[1]['value']);
    }

    public function testDatePickerMessage()
    {
        /** @lang XML */
        $markup = <<<EOT
<message disable_text="1">
    <date-picker-message>
        <text>Text</text>
        <submit_text>Submit</submit_text>
        <callback>Callback</callback>
        <max_date>today</max_date>
        <min_date>20200101</min_date>
        <year_required>true</year_required>
        <month_required>true</month_required>
        <day_required>false</day_required>
        <attribute_name>Attribute</attribute_name>
    </date-picker-message>
</message>
EOT;
        $formatter = new WebChatMessageFormatter();

        /** @var OpenDialogMessage[] $messages */
        $messages = $formatter->getMessages($markup)->getMessages();
        $this->assertEquals('Text', $messages[0]->getData()['text']);
        $this->assertEquals('Submit', $messages[0]->getData()['submit_text']);
        $this->assertEquals('Callback', $messages[0]->getData()['callback']);
        $this->assertEquals('Callback', $messages[0]->getData()['callback']);
        $this->assertEquals('today', $messages[0]->getData()['max_date']);
        $this->assertEquals('20200101', $messages[0]->getData()['min_date']);
        $this->assertTrue($messages[0]->getData()['year_required']);
        $this->assertTrue($messages[0]->getData()['month_required']);
        $this->assertFalse($messages[0]->getData()['day_required']);
        $this->assertEquals('Attribute', $messages[0]->getData()['attribute_name']);
    }
}
