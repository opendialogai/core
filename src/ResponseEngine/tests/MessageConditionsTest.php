<?php

namespace OpenDialogAi\Core\ResponseEngine\tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConditionsYamlGenerator;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class MessageConditionsTest extends TestCase
{
    /** @var OutgoingIntent */
    private $intent;

    /** @var ResponseEngineServiceInterface */
    private $responseEngineService;

    public function setUp(): void
    {
        parent::setUp();

        /** @var OutgoingIntent $intent */
        $this->intent = OutgoingIntent::create(['name' => 'test']);

        $this->setConfigValue('opendialog.context_engine.custom_attributes',
            ['false' => BooleanAttribute::class]);

        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $contextService->getContext('session')->addAttribute(AttributeResolver::getAttributeFor('false', false));

        $this->responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
    }

    public function testFinalPassingCondition()
    {
        $failingMessage = (new MessageMarkUpGenerator())->addTextMessage('Should not pass');
        $failingCondition = (new ConditionsYamlGenerator())
            ->addCondition('session.false', 'true', 'eq')
            ->addCondition('session.false', 'false', 'eq');

        $this->intent->messageTemplates()->create([
            'name' => 'should not pass',
            'conditions' => $failingCondition->getYaml(),
            'message_markup' => $failingMessage->getMarkUp()]
        );

        // Should throw No Matching Message Exception
        $this->expectException(NoMatchingMessagesException::class);
        $this->responseEngineService->getMessageForIntent('test');
    }

    public function testFinalFailingCondition()
    {
        $failingMessage = (new MessageMarkUpGenerator())->addTextMessage('Should not pass');
        $failingCondition = (new ConditionsYamlGenerator())
            ->addCondition('session.false', 'false', 'eq')
            ->addCondition('session.false', 'true', 'eq');

        $this->intent->messageTemplates()->create([
            'name' => 'should not pass',
            'conditions' => $failingCondition->getYaml(),
            'message_markup' => $failingMessage->getMarkUp()]
        );

        // Should throw No Matching Message Exception
        $this->expectException(NoMatchingMessagesException::class);
        $this->responseEngineService->getMessageForIntent('test');
    }

    public function testOnlyFailingCondition()
    {
        $failingMessage = (new MessageMarkUpGenerator())->addTextMessage('Should not pass');
        $failingCondition = (new ConditionsYamlGenerator())
            ->addCondition('session.false', 'true', 'eq');

        $this->intent->messageTemplates()->create([
            'name' => 'should not pass',
            'conditions' => $failingCondition->getYaml(),
            'message_markup' => $failingMessage->getMarkUp()]
        );

        // Should throw No Matching Message Exception
        $this->expectException(NoMatchingMessagesException::class);
        $this->responseEngineService->getMessageForIntent('test');
    }

}
