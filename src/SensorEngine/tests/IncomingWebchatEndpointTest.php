<?php

namespace OpenDialogAi\SensorEngine\Tests;

use OpenDialogAi\Core\Tests\TestCase;

class IncomingWebchatEndpointTest extends TestCase
{
    /**
     * Test top-level parameter validation.
     */
    public function testRequiredParams()
    {
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
     * Test message content validation.
     */
    public function testMessageContent()
    {
        // Ensure that the content field is required for messages.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
        ]);
        $response
            ->assertStatus(422)
            ->assertJson(['errors' => ['content' => ['The content field is required when notification is message.']]]);


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
            ->assertStatus(400)
            ->assertJson(['type' => ['The selected type is invalid.']]);

        // Ensure that a valid message is validated and gives the correct response.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'text',
                'data' => [
                    'Hello'
                ],
            ],
        ]);
        $response
            ->assertStatus(200)
            ->assertJson([
                'author' => 'them',
                'type' => 'text',
                'data' => [
                    'text' => 'Hello',
                ],
            ]);
    }
}
