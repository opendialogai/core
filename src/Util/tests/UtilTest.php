<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Util\RequestLog;
use OpenDialogAi\Util\ResponseLog;
use OpenDialogAi\Core\Tests\TestCase;

class UtilTest extends TestCase
{
    /**
     * Test that incoming & outgoing messages are logged.
     */
    public function testApiLogging()
    {
        if (!getenv('LOCAL')) {
            // This test depends on dGraph.
            $this->markTestSkipped('This test only runs on local environments.');
        }

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
            ->assertJson([0 => ['data' => ['text' => 'No messages found for intent intent.core.NoMatchResponse']]]);

        // Ensure that the request was logged.
				$this->assertDatabaseHas('request_logs', [
						'url' => 'http://localhost/incoming/webchat',
						'method' => 'POST',
						'source_ip' => '127.0.0.1',
				]);

        // Ensure that responses are logged.
				$this->assertDatabaseHas('response_logs', [
						'http_status' => 200
				]);
    }
}
