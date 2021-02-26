<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use Mockery\MockInterface;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserDataClient;
use OpenDialogAi\ConversationEngine\Exceptions\IncomingUtteranceNotValid;
use OpenDialogAi\ConversationEngine\Reasoners\UtteranceReasoner;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UserGenerator;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class UtteranceReasonerTest extends TestCase
{
    public function testUtteranceAnalysisWhenMissingUserId()
    {
        $utterance = UtteranceGenerator::generateUtteranceWithoutUserId();
        $this->expectException(IncomingUtteranceNotValid::class);
        UtteranceReasoner::analyseUtterance($utterance);
    }

    public function testUtteranceAnalysisWithUserId()
    {
        $utterance = UtteranceGenerator::generateChatOpenUtterance(
            'intent.core.welcome',
            UserGenerator::generateUtteranceUserWithCustomAttributes()
        );

        $this->mock(UserDataClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('loadAttribute')
                ->once()
                ->andReturn(UserGenerator::generateCurrentUserWithCustomAttributes()
            );
        });

        $userAttribute = UtteranceReasoner::analyseUtterance($utterance);

        $this->assertEquals(UserAttribute::CURRENT_USER, $userAttribute->getId());
    }
}
