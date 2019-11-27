<?php

namespace OpenDialogAi\Core\Tests;

use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\Core\ResponseLog;

class CoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->activateConversation($this->conversation4());
    }

    /**
     * Test that requests to the incoming endpoint are logged.
     */
    public function testApiLogging()
    {
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'text',
                'data' => [
                    'text' => 'test message'
                ],
                'user' => [
                    'ipAddress' => '127.0.0.1',
                    'country' => 'UK',
                    'browserLanguage' => 'en-gb',
                    'os' => 'macos',
                    'browser' => 'safari',
                    'timezone' => 'GMT',
                ],
            ],
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(['data' => ['text' => 'No messages found for intent intent.core.NoMatchResponse']]);

        // Ensure that the request was logged.
        $this->assertDatabaseHas('request_logs', [
            'method' => 'POST',
            'source_ip' => '127.0.0.1',
        ]);
        $this->assertEquals(1, RequestLog::all()->count());

        // Ensure that responses are logged.
        $this->assertDatabaseHas('response_logs', [
            'http_status' => 200
        ]);
        $this->assertEquals(1, ResponseLog::all()->count());

        // Check that requests/responses are not logged for non-incoming endpoints.
        $response = $this->json('GET', '/config');
        $response->assertStatus(200);
        $this->assertEquals(1, RequestLog::all()->count());
        $this->assertEquals(1, ResponseLog::all()->count());
    }
}
