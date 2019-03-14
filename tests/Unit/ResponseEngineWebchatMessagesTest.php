<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormSelectElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormTextAreaElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormTextElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatListElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatLongTextMessage;

class ResponseEngineWebchatMessagesTest extends TestCase
{
    public function testEmptyMessage()
    {
        $message = new EmptyMessage();
        $this->assertEquals(true, $message->isEmpty());
    }

    public function testWebChatMessage()
    {
        $message = new WebChatMessage();
        $message->setText('This is a test, this is only a test.');
        $this->assertEquals('This is a test, this is only a test.', $message->getText());
    }

    public function testWebChatLongTextMessage()
    {
        $message = new WebChatLongTextMessage();
        $message->setInitialText('This is a test, this is only a test.');
        $message->setConfirmationText('This is a test, this is only a confirmation test.');
        $message->setSubmitText('This is a test, this is only a submission test.');
        $this->assertEquals('This is a test, this is only a confirmation test.', $message->getConfirmationText());
        $this->assertEquals('This is a test, this is only a submission test.', $message->getSubmitText());
    }

    public function testWebChatListMessage()
    {
        $message = new WebChatListMessage();
        $listElement1 = new WebChatListElement('Element title', 'Some subtext here', null);
        $message->addElement($listElement1);
        $listElement2 = new WebChatListElement('Second title', null, null);
        $listElement2->setButtonText('click me');
        $listElement2->setButtonUrl('http://www.opendialog.ai');
        $message->addElement($listElement2);

        $expectedOutput = [
            [
                'title' => 'Element title',
                'subtitle' => 'Some subtext here',
                'image' => NULL,
                'button' => [
                    'text' => NULL,
                    'callback' => NULL,
                    'url' => NULL,
                    'link_new_tab' => true,
                ],
            ],
            [
                'title' => 'Second title',
                'subtitle' => NULL,
                'image' => NULL,
                'button' => [
                  'text' => 'click me',
                  'callback' => NULL,
                  'url' => 'http://www.opendialog.ai',
                  'link_new_tab' => true,
                ],
            ],
        ];

        $this->assertEquals($expectedOutput, $message->getElementsArray());
    }

    public function testWebChatImageMessage()
    {
        $message = new WebChatImageMessage();
        $message->setImgLink('http://www.opendialog.ai/');
        $message->setImgSrc('http://www.opendialog.ai/assets/images/logo.svg');
        $message->setLinkNewTab(false);
        $this->assertEquals('http://www.opendialog.ai/', $message->getImgLink());
        $this->assertEquals('http://www.opendialog.ai/assets/images/logo.svg', $message->getImgSrc());
        $this->assertEquals(false, $message->getLinkNewTab());
    }

    public function testWebChatFormMessage()
    {
        $message = new WebChatFormMessage();
        $element1 = new WebChatFormTextElement('name', 'Enter your Name', true);
        $element2 = new WebChatFormSelectElement('question', 'Do you love OpenDialog?', true, ['yes', 'very yes']);
        $element3 = new WebChatFormTextAreaElement('tell_more', 'Tell me more about yourself');
        $message->addElement($element1);
        $message->addElement($element2);
        $message->addElement($element3);

        $expectedOutput = [
            [
								'name' => 'name',
								'display' => 'Enter your Name',
								'required' => true,
								'element_type' => 'text',
            ],
            [
								'name' => 'question',
								'display' => 'Do you love OpenDialog?',
								'required' => true,
								'element_type' => 'select',
								'options' => [
                    0 => 'yes',
                    1 => 'very yes',
								],
            ],
            [
								'name' => 'tell_more',
								'display' => 'Tell me more about yourself',
								'required' => false,
								'element_type' => 'textarea',
            ],
        ];

        $this->assertEquals($expectedOutput, $message->getElementsArray());
    }

    public function testWebChatButtonMessage()
    {
        $message = new WebChatButtonMessage();
        $button1 = new WebChatButton('Yes', 'callback_yes', true);
        $button2 = new WebChatButton('No', 'callback_no', false);
        $message->addButton($button1);
        $message->addButton($button2);

        $expectedOutput = [
						[
								'text' => 'Yes',
								'callback_id' => 'callback_yes',
								'value' => true,
						],
						[
								'text' => 'No',
								'callback_id' => 'callback_no',
								'value' => false,
						],
        ];

        $this->assertEquals($expectedOutput, $message->getButtonsArray());
    }
}
