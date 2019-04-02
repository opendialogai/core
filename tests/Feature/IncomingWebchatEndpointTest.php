<?php

namespace OpenDialogAi\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use OpenDialogAi\Core\Tests\TestCase;

class IncomingWebchatEndpointTest extends TestCase
{
    public function testRequiredParams()
    {
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson(['author' => ['The author field is required.']]);

        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'author' => 'me',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson(['user_id' => ['The user id field is required.']]);

        $response = $this->json('POST', '/incoming/webchat', [
            'user_id' => 'someuser',
            'author' => 'me',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson(['notification'  => ['The notification field is required.']]);
    }
}
