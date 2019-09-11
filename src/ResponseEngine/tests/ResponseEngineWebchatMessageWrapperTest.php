<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Message\Message;

class ResponseEngineWebchatMessageWrapperTest extends TestCase
{
    public function testMessageWrapperEmpty()
    {
        $messageWrapper = new OpenDialogMessages();
        $this->assertEquals([], $messageWrapper->getMessages());
    }

    public function testMessageWrapperGetMessages()
    {
        $messageWrapper = new OpenDialogMessages();
        $message1 = new Message();
        $message1->setText('This is a test, this is only a test.');
        $messageWrapper->addMessage($message1);
        $message2 = new Message();
        $message2->setText('This is another test, this is only another test.');
        $messageWrapper->addMessage($message2);
        foreach ($messageWrapper->getMessages() as $message) {
            $this->assertInstanceOf(Message::class, $message);
        }
    }

    public function testMessageWrapperGetMessageToPost()
    {
        $messageWrapper = new OpenDialogMessages();
        $message1 = new Message();
        $message1->setText('This is a test, this is only a test.');
        $messageWrapper->addMessage($message1);
        $message2 = new Message();
        $message2->setText('This is another test, this is only another test.');
        $messageWrapper->addMessage($message2);
        $this->assertArraySubset([0 => ['data' => ['text' => 'This is a test, this is only a test.']]], $messageWrapper->getMessageToPost());
        $this->assertArraySubset([1 => ['data' => ['text' => 'This is another test, this is only another test.']]], $messageWrapper->getMessageToPost());
    }
}
