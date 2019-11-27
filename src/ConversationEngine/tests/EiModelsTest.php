<?php

namespace OpenDialogAi\ConversationEngine\tests;

use Exception;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;
use OpenDialogAi\Core\Tests\TestCase;

class EiModelsTest extends TestCase
{
    /**
     * @var EIModelCreator
     */
    private $eiModelCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->eiModelCreator = app()->make(EIModelCreator::class);
    }

    public function testEiModelCreatorWithInvalidClassName() {
        $this->expectException(Exception::class);
        $this->eiModelCreator->createEIModel("invalidClassName", []);
    }

    public function testEiModelCreatorWithInvalidClassInheritence() {
        $this->expectException(Exception::class);
        $this->eiModelCreator->createEIModel(EiModelsTest::class, []);
    }

    public function testEiModelCreatorWithInvalidResponse() {
        $this->expectException(Exception::class);
        $response = [["invalid_key" => "invalid_value"]];
        $this->eiModelCreator->createEIModel(EIModelOpeningIntents::class, $response);
    }
}
