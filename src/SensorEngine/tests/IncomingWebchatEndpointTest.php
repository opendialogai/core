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
            ->assertJson(['errors' => ['content.data.callback_id' => ['The content.data.callback id field is required when content.type is chat_open.']]]);


        // Ensure that a valid chat_open message is validated and gives the correct response.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'type' => 'chat_open',
                'callback_id' => 'me',
                'user' => [
                    'ip' => '127.0.0.1',
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
