<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Reasoners\ScenarioSelector;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConversationGenerator;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class ConversationEngineTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group skip
     */
    public function testScenarioSelector()
    {

    }
}
