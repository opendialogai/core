<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\CallbackButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormSelectElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormTextAreaElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormTextElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatEmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatFullPageFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatLongTextMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatTextMessage;

class ResponseEngineWebchatMessagesTest extends TestCase
{
    public function testEmptyMessage()
    {
        $message = new WebchatEmptyMessage();
        $message->setDisableText(true);
        $this->assertEquals(true, $message->isEmpty());
        $this->assertEquals(1, $message->getData()['disable_text']);
    }

    public function testWebChatMessage()
    {
        $message = new WebchatTextMessage();
        $message->setText('This is a test, this is only a test.');
        $message->setDisableText(true);
        $this->assertEquals(1, $message->getData()['disable_text']);
        $this->assertEquals('This is a test, this is only a test.', $message->getText());
    }

    public function testWebChatLongTextMessage()
    {
        $message = new WebchatLongTextMessage();
        $message->setInitialText('This is a test, this is only a test.');
        $message->setConfirmationText('This is a test, this is only a confirmation test.');
        $message->setSubmitText('This is a test, this is only a submission test.');
        $message->setDisableText(true);
        $this->assertEquals(1, $message->getData()['disable_text']);
        $this->assertEquals('This is a test, this is only a test.', $message->getInitialText());
        $this->assertEquals('This is a test, this is only a confirmation test.', $message->getConfirmationText());
        $this->assertEquals('This is a test, this is only a submission test.', $message->getSubmitText());
    }

    public function testWebChatListMessage()
    {
        $message = new WebchatListMessage();
        $message->setDisableText(false);
        $message->addItem((new WebchatTextMessage())->setText('This is a test, this is only a test.'));
        $message->addItem((new WebchatImageMessage()));
        $message->addItem((new WebchatButtonMessage())->setText('Yes'));

        $items = $message->getItemsArray();

        $this->assertEquals(0, $message->getData()['disable_text']);
        $this->assertEquals(3, count($items));
        $this->assertEquals('text', $items[0]['message_type']);
        $this->assertEquals('image', $items[1]['message_type']);
        $this->assertEquals('button', $items[2]['message_type']);
    }

    public function testWebChatImageMessage()
    {
        $message = new WebchatImageMessage();
        $message->setImgLink('http://www.opendialog.ai/');
        $message->setImgSrc('http://www.opendialog.ai/assets/images/logo.svg');
        $message->setLinkNewTab(false);
        $message->setDisableText(false);
        $this->assertEquals(0, $message->getData()['disable_text']);
        $this->assertEquals('http://www.opendialog.ai/', $message->getImgLink());
        $this->assertEquals('http://www.opendialog.ai/assets/images/logo.svg', $message->getImgSrc());
        $this->assertEquals(false, $message->getLinkNewTab());
        $this->assertEquals(0, $message->getData()['link_new_tab']);
    }

    public function testWebChatFormMessage()
    {
        $message = new WebchatFormMessage();
        $element1 = new FormTextElement('name', 'Enter your Name', true);
        $element2 = new FormSelectElement('question', 'Do you love OpenDialog?', true, ['yes', 'very yes']);
        $element3 = new FormTextAreaElement('tell_more', 'Tell me more about yourself');
        $message->setDisableText(false);
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

        $this->assertEquals(0, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getElementsArray());
    }

    public function testWebChatFullPageFormMessage()
    {
        $message = new WebchatFullPageFormMessage();
        $element1 = new FormTextElement('name', 'Enter your Name', true);
        $element2 = new FormSelectElement('question', 'Do you love OpenDialog?', true, ['yes', 'very yes']);
        $element3 = new FormTextAreaElement('tell_more', 'Tell me more about yourself');
        $message->setDisableText(false);
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

        $this->assertEquals(0, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getElementsArray());
    }

    public function testWebChatButtonMessage()
    {
        $message = new WebchatButtonMessage();
        $message->setClearAfterInteraction(false);
        $button1 = new CallbackButton('Yes', 'callback_yes', true);
        $button2 = new CallbackButton('No', 'callback_no', false);
        $message->addButton($button1);
        $message->addButton($button2);
        $message->setDisableText(false);

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

        $this->assertEquals(0, $message->getData()['clear_after_interaction']);
        $this->assertEquals(0, $message->getData()['disable_text']);
        $this->assertEquals($expectedOutput, $message->getButtonsArray());
    }
}
