<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class UserAttributeTest extends TestCase
{
    public function testUserAttributes()
    {
        $utterance = UtteranceGenerator::generateChatOpenUtterance('WELCOME');
        $this->activateConversation($this->noMatchConversation());

        resolve(OpenDialogController::class)->runConversation($utterance);

        $this->assertTrue(ContextService::getUserContext()->getUser()->hasAttribute(Model::EI_TYPE));
        $this->assertTrue(ContextService::getUserContext()->getUser()->hasUserAttribute('first_name'));
    }
}
