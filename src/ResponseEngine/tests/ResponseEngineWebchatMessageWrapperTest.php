<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;

class ResponseEngineWebchatMessageWrapperTest extends TestCase
{
    use ArraySubsetAsserts;

    public function testMessageWrapperEmpty()
    {
        $messageWrapper = new WebChatMessages();
        $this->assertEquals([], $messageWrapper->getMessages());
    }

    public function testMessageWrapperGetMessages()
    {
        $messageWrapper = new WebChatMessages();
        $message1 = new WebChatMessage();
        $message1->setText('This is a test, this is only a test.');
        $messageWrapper->addMessage($message1);
        $message2 = new WebChatMessage();
        $message2->setText('This is another test, this is only another test.');
        $messageWrapper->addMessage($message2);
        foreach ($messageWrapper->getMessages() as $message) {
            $this->assertInstanceOf(WebChatMessage::class, $message);
        }
    }

    public function testMessageWrapperGetMessageToPost()
    {
        $messageWrapper = new WebChatMessages();
        $message1 = new WebChatMessage();
        $message1->setText('This is a test, this is only a test.');
        $messageWrapper->addMessage($message1);
        $message2 = new WebChatMessage();
        $message2->setText('This is another test, this is only another test.');
        $messageWrapper->addMessage($message2);

        $this->assertArraySubset(
            [0 => ['data' => ['text' => 'This is a test, this is only a test.']]],
            $messageWrapper->getMessageToPost()
        );
        $this->assertArraySubset(
            [1 => ['data' => ['text' => 'This is another test, this is only another test.']]],
            $messageWrapper->getMessageToPost()
        );
    }
}
