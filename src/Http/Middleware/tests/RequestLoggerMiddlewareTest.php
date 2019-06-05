<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Tests\TestCase;

class RequestLoggerMiddlewareTest extends TestCase
{
    public function testRequestLoggerMiddleware()
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
                'type' => 'chat_open',
                'data' => [
                    'callback_id' => 'welcome',
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

        $this->assertDatabaseHas('request_logs', [
            'url' => 'http://localhost/incoming/webchat',
            'method' => 'POST',
            'source_ip' => '127.0.0.1',
            'raw_request' => '{"notification":"message","user_id":"someuser","author":"me","content":{"author":"me","type":"chat_open","data":{"callback_id":"welcome"},"user":{"ipAddress":"127.0.0.1","country":"UK","browserLanguage":"en-gb","os":"macos","browser":"safari","timezone":"GMT"}}}',
        ]);

        $this->assertDatabaseHas('response_logs', [
            'http_status' => '200',
        ]);
    }
}
