<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;

class ResponseEngineWebchatMessageWrapperTest extends TestCase
{
    use ArraySubsetAsserts;

    public function testMessageWrapperEmpty()
    {
        $messageWrapper = new OpenDialogMessages();
        $this->assertEquals([], $messageWrapper->getMessages());
    }

    public function testMessageWrapperGetMessages()
    {
        $messageWrapper = new OpenDialogMessages();
        $message1 = new OpenDialogMessage();
        $message1->setText('This is a test, this is only a test.');
        $messageWrapper->addMessage($message1);
        $message2 = new OpenDialogMessage();
        $message2->setText('This is another test, this is only another test.');
        $messageWrapper->addMessage($message2);
        foreach ($messageWrapper->getMessages() as $message) {
            $this->assertInstanceOf(OpenDialogMessage::class, $message);
        }
    }

    public function testMessageWrapperGetMessageToPost()
    {
        $messageWrapper = new OpenDialogMessages();
        $message1 = new OpenDialogMessage();
        $message1->setText('This is a test, this is only a test.');
        $messageWrapper->addMessage($message1);
        $message2 = new OpenDialogMessage();
        $message2->setText('This is another test, this is only another test.');
        $messageWrapper->addMessage($message2);
        self::assertArraySubset([0 => ['data' => ['text' => 'This is a test, this is only a test.']]], $messageWrapper->getMessageToPost(), true);
        self::assertArraySubset([1 => ['data' => ['text' => 'This is another test, this is only another test.']]], $messageWrapper->getMessageToPost(), true);
    }
}
