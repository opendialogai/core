<?php

namespace OpenDialogAi\ContextManager\Tests;

use Mockery;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTriggerUtterance;

class UserServiceUpdateUserFromUtteranceTest extends TestCase
{
    /* @var AttributeResolver */
    private $attributeResolver;

    /* @var DGraphClient */
    private $dGraphClient;

    public function setUp(): void
    {
        parent::setUp();

        Mockery::globalHelpers();

        $this->attributeResolver = new AttributeResolver();

        $attributes = [
            'custom_1' => StringAttribute::class,
            'custom_2' => StringAttribute::class,
        ];

        $this->attributeResolver->registerAttributes($attributes);

        $dGraphMutationResponse = mock(DGraphMutationResponse::class)->makePartial();
        $dGraphMutationResponse->shouldReceive('isSuccessful')->andReturn(true);

        $this->dGraphClient = mock(DGraphClient::class)->makePartial();
        $this->dGraphClient->shouldReceive('tripleMutation')->andReturn($dGraphMutationResponse);
    }

    public function testUpdateUserFromUtteranceWithWebchatChatOpenUtterance()
    {
        $userService = mock(UserService::class, [$this->dGraphClient])->makePartial();
        $userService->setAttributeResolver($this->attributeResolver);

        $chatbotUser = new ChatbotUser();

        $chatbotUser->addAttribute(new StringAttribute('first_name', ''));
        $chatbotUser->addAttribute(new StringAttribute('last_name', ''));
        $chatbotUser->addAttribute(new StringAttribute('email', ''));
        $chatbotUser->addAttribute(new StringAttribute('external_id', ''));

        $utteranceUser = new User('1');

        $utteranceUser->setFirstName('test');
        $utteranceUser->setLastName('test');
        $utteranceUser->setEmail('test@example.com');
        $utteranceUser->setExternalId('1');
        $utteranceUser->setCustomParameters([ 'custom_1' => 'value_1', 'custom_2' => 'value_2' ]);

        $userService->shouldReceive('getUser')->andReturn($chatbotUser);

        $utterance = new WebchatChatOpenUtterance();
        $utterance->setUser($utteranceUser);
        $utterance->setUserId('1');

        $this->assertEquals($chatbotUser->getAttribute('first_name')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('last_name')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('email')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('external_id')->getValue(), '');

        $user = $userService->updateUserFromUtterance($utterance);

        // Ensure that the user is stored to the DB.
        $this->assertDatabaseHas('chatbot_users', [
            'user_id' => '1',
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals($chatbotUser->getAttribute('first_name')->getValue(), $utteranceUser->getFirstName());
        $this->assertEquals($chatbotUser->getAttribute('last_name')->getValue(), $utteranceUser->getLastName());
        $this->assertEquals($chatbotUser->getAttribute('email')->getValue(), $utteranceUser->getEmail());
        $this->assertEquals($chatbotUser->getAttribute('external_id')->getValue(), $utteranceUser->getExternalId());

        $this->assertEquals($chatbotUser->getAttribute('custom_1')->getValue(), 'value_1');
        $this->assertEquals($chatbotUser->getAttribute('custom_2')->getValue(), 'value_2');
    }

    public function testUpdateUserFromUtteranceWithWebchatTextUtterance()
    {
        $userService = mock(UserService::class, [$this->dGraphClient])->makePartial();
        $userService->setAttributeResolver($this->attributeResolver);

        $chatbotUser = new ChatbotUser();

        $chatbotUser->addAttribute(new StringAttribute('first_name', ''));
        $chatbotUser->addAttribute(new StringAttribute('last_name', ''));
        $chatbotUser->addAttribute(new StringAttribute('email', ''));
        $chatbotUser->addAttribute(new StringAttribute('external_id', ''));

        $utteranceUser = new User('1');

        $utteranceUser->setFirstName('test');
        $utteranceUser->setLastName('test');
        $utteranceUser->setEmail('test@example.com');
        $utteranceUser->setExternalId('1');
        $utteranceUser->setCustomParameters([ 'custom_1' => 'value_1', 'custom_2' => 'value_2' ]);

        $userService->shouldReceive('getUser')->andReturn($chatbotUser);

        $utterance = new WebchatTextUtterance();
        $utterance->setUser($utteranceUser);
        $utterance->setUserId('1');

        $this->assertEquals($chatbotUser->getAttribute('first_name')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('last_name')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('email')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('external_id')->getValue(), '');

        $user = $userService->updateUserFromUtterance($utterance);

        $this->assertEquals($chatbotUser->getAttribute('first_name')->getValue(), $utteranceUser->getFirstName());
        $this->assertEquals($chatbotUser->getAttribute('last_name')->getValue(), $utteranceUser->getLastName());
        $this->assertEquals($chatbotUser->getAttribute('email')->getValue(), $utteranceUser->getEmail());
        $this->assertEquals($chatbotUser->getAttribute('external_id')->getValue(), $utteranceUser->getExternalId());

        $this->assertEquals($chatbotUser->getAttribute('custom_1')->getValue(), 'value_1');
        $this->assertEquals($chatbotUser->getAttribute('custom_2')->getValue(), 'value_2');
    }

    public function testUpdateUserFromUtteranceWithWebchatTriggerUtterance()
    {
        $userService = mock(UserService::class, [$this->dGraphClient])->makePartial();
        $userService->setAttributeResolver($this->attributeResolver);

        $chatbotUser = new ChatbotUser();

        $chatbotUser->addAttribute(new StringAttribute('first_name', ''));
        $chatbotUser->addAttribute(new StringAttribute('last_name', ''));
        $chatbotUser->addAttribute(new StringAttribute('email', ''));
        $chatbotUser->addAttribute(new StringAttribute('external_id', ''));

        $utteranceUser = new User('1');

        $utteranceUser->setFirstName('test');
        $utteranceUser->setLastName('test');
        $utteranceUser->setEmail('test@example.com');
        $utteranceUser->setExternalId('1');
        $utteranceUser->setCustomParameters([ 'custom_1' => 'value_1', 'custom_2' => 'value_2' ]);

        $userService->shouldReceive('getUser')->andReturn($chatbotUser);

        $utterance = new WebchatTriggerUtterance();
        $utterance->setUser($utteranceUser);
        $utterance->setUserId('1');

        $this->assertEquals($chatbotUser->getAttribute('first_name')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('last_name')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('email')->getValue(), '');
        $this->assertEquals($chatbotUser->getAttribute('external_id')->getValue(), '');

        $user = $userService->updateUserFromUtterance($utterance);

        // Ensure that the user is stored to the DB.
        $this->assertDatabaseHas('chatbot_users', [
            'user_id' => '1',
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals($chatbotUser->getAttribute('first_name')->getValue(), $utteranceUser->getFirstName());
        $this->assertEquals($chatbotUser->getAttribute('last_name')->getValue(), $utteranceUser->getLastName());
        $this->assertEquals($chatbotUser->getAttribute('email')->getValue(), $utteranceUser->getEmail());
        $this->assertEquals($chatbotUser->getAttribute('external_id')->getValue(), $utteranceUser->getExternalId());

        $this->assertEquals($chatbotUser->getAttribute('custom_1')->getValue(), 'value_1');
        $this->assertEquals($chatbotUser->getAttribute('custom_2')->getValue(), 'value_2');
    }
}
