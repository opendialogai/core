<?php

namespace OpenDialogAi\SensorEngine\Tests;

use OpenDialogAi\Core\SensorEngine\tests\WebchatSensorTestBase;

class IncomingWebchatEndpointTest extends WebchatSensorTestBase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @requires DGRAPH
     *
     * Test top-level parameter validation.
     */
    public function testRequiredParams()
    {
        $this->activateConversation($this->conversation4());

        // Ensure that the author field is required.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['author' => ['The author field is required.']]]);

        // Ensure that the user_id field is required.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'author' => 'me',
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['user_id' => ['The user id field is required.']]]);

        // Ensure that the notification type field is required.
        $response = $this->json('POST', '/incoming/webchat', [
            'user_id' => 'someuser',
            'author' => 'me',
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['notification' => ['The notification field is required.']]]);
    }

    /**
     * @requires DGRAPH
     *
     * Test message content validation.
     */
    public function testMessageContent()
    {
        $this->activateConversation($this->conversation4());

        // Ensure that the content field is required for messages.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['content' => ['The content field is required.']]]);

        // Ensure that the notification type is validated.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'some_new_type',
            'user_id' => 'someuser',
            'author' => 'me',
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['notification' => ['The selected notification is invalid.']]]);

        // Ensure that the message type is validated.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'foo',
                'data' => [
                    'test'
                ],
            ],
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['content.type' => ['The selected content.type is invalid.']]]);

        // Ensure that a callback is required for chat_open messages.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'chat_open',
            ],
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['content.callback_id' => ['The content.callback id field is required when content.type is chat_open.']]]);
    }

    /**
     * @requires DGRAPH
     *
     * Test message response.
     */
    public function testMessageResponse()
    {
        $this->activateConversation($this->conversation4());

        // Test a valid message.
        $response = $this->json('POST', '/incoming/webchat', $this->generateResponseMessage('text', [
            'text' => 'test'
        ]));

        $response
            ->assertStatus(200)
            ->assertJson(['data' => ['text' => 'No messages found for intent intent.core.NoMatchResponse']]);

        //@todo full valid tests need to mock ODController responses.
    }
}
