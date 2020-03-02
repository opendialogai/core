<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTriggerUtterance;

class UtteranceTest extends TestCase
{
    public function testWebchatChatOpenUtterance()
    {
        $utterance = new WebchatChatOpenUtterance();

        try {
            $utterance->setUserId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getUserId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setText('test');
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        try {
            $utterance->getText();
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        try {
            $utterance->setUser(new User('1'));
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getUser();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setMessageId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getMessageId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setTimestamp(1000000);
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getTimestamp();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setCallbackId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getCallbackId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getValue();
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        $this->assertTrue(true);
    }

    public function testWebchatTextUtterance()
    {
        $utterance = new WebchatTextUtterance();

        try {
            $utterance->setUserId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getUserId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setText('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getText();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setMessageId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getMessageId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setTimestamp(1000000);
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getTimestamp();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setValue('test');
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        try {
            $utterance->getValue();
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        $this->assertTrue(true);
    }

    public function testWebchatTriggerUtterance()
    {
        $utterance = new WebchatTriggerUtterance();

        try {
            $utterance->setUserId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getUserId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setText('test');
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        try {
            $utterance->getText();
            $this->fail('Exception not thrown');
        } catch (FieldNotSupported $e) {
        }

        try {
            $utterance->setUser(new User('1'));
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getUser();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setMessageId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getMessageId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setTimestamp(1000000);
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getTimestamp();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setCallbackId('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getCallbackId();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->setValue('test');
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        try {
            $utterance->getValue();
        } catch (FieldNotSupported $e) {
            $this->fail('Exception thrown');
        }

        $this->assertTrue(true);
    }
}
