<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserDataClient;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\Exceptions\IncomingUtteranceNotValid;
use OpenDialogAi\ConversationEngine\Reasoners\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Reasoners\UtteranceReasoner;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Tests\ConversationGenerator;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UserGenerator;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class ConversationEngineTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    /* @var UtteranceAttribute */
    private $utterance;

    public function setUp(): void
    {
        parent::setUp();

        $this->conversationEngine = resolve(ConversationEngineInterface::class);

        $this->utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
    }

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
            UserGenerator::generateUtteranceUserWithCustomAttributes());

        $this->mock(UserDataClient::class, function ($mock) {
            $mock->shouldReceive('loadAttribute')->once()
                ->andReturn(UserGenerator::generateCurrentUserWithCustomAttributes()
            );
        });

        $userAttribute = UtteranceReasoner::analyseUtterance($utterance);
        $this->assertEquals(UserAttribute::CURRENT_USER, $userAttribute->getId());

    }

    public function testScenarioSelector()
    {
        ConversationDataClient::shouldReceive('getAllActiveScenarios')->once()
            ->andReturn(ConversationGenerator::generateScenariosWithEverything('test'), 1);

        $conditionPassingScenarios = ScenarioSelector::selectActiveScenarios();
    }

}
