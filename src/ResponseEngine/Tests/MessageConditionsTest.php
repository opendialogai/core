<?php

namespace OpenDialogAi\Core\ResponseEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConditionsYamlGenerator;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
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

        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['false' => BooleanAttribute::class]
        );

        ContextService::getContext('session')->addAttribute(AttributeResolver::getAttributeFor('false', false));

        $this->responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
    }

    /**
     * @group skip
     * @throws NoMatchingMessagesException
     */
    public function testFinalPassingCondition()
    {
        $attributes = ['sessionfalse' => 'session.false'];

        $failingMessage = (new MessageMarkUpGenerator())->addTextMessage('Should not pass');
        $failingCondition = (new ConditionsYamlGenerator())
            ->addCondition($attributes, ['value' => 'true'], 'eq')
            ->addCondition($attributes, ['value' => 'false'], 'eq');

        $this->intent->messageTemplates()->create(
            [
            'name' => 'should not pass',
            'conditions' => $failingCondition->getYaml(),
            'message_markup' => $failingMessage->getMarkUp()]
        );

        // Should throw No Matching Message Exception
        $this->expectException(NoMatchingMessagesException::class);
        $this->responseEngineService->getMessageForIntent('webchat', 'test');
    }

    public function testFinalFailingCondition()
    {
        $attributes = ['sessionfalse' => 'session.false'];

        $failingMessage = (new MessageMarkUpGenerator())->addTextMessage('Should not pass');
        $failingCondition = (new ConditionsYamlGenerator())
            ->addCondition($attributes, ['value' => 'false'], 'eq')
            ->addCondition($attributes, ['value' => 'true'], 'eq');

        $this->intent->messageTemplates()->create(
            [
            'name' => 'should not pass',
            'conditions' => $failingCondition->getYaml(),
            'message_markup' => $failingMessage->getMarkUp()]
        );

        // Should throw No Matching Message Exception
        $this->expectException(NoMatchingMessagesException::class);
        $this->responseEngineService->getMessageForIntent('webchat', 'test');
    }

    public function testOnlyFailingCondition()
    {
        $attributes = ['sessionfalse' => 'session.false'];

        $failingMessage = (new MessageMarkUpGenerator())->addTextMessage('Should not pass');
        $failingCondition = (new ConditionsYamlGenerator())
            ->addCondition($attributes, ['value' => 'true'], 'eq');

        $this->intent->messageTemplates()->create(
            [
            'name' => 'should not pass',
            'conditions' => $failingCondition->getYaml(),
            'message_markup' => $failingMessage->getMarkUp()]
        );

        // Should throw No Matching Message Exception
        $this->expectException(NoMatchingMessagesException::class);
        $this->responseEngineService->getMessageForIntent('webchat', 'test');
    }
}
