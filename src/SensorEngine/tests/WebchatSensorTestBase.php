<?php

namespace OpenDialogAi\Core\SensorEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;

Abstract class WebchatSensorTestBase extends TestCase
{
    /**
     * @param $type
     * @param $data
     * @return array
     */
    protected function generateResponseMessage($type, $data): array
    {
        return [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => $type,
                'data' => $data,
                'user' => [
                    'ipAddress' => '127.0.0.1',
                    'country' => 'UK',
                    'browserLanguage' => 'en-gb',
                    'os' => 'macos',
                    'browser' => 'safari',
                    'timezone' => 'GMT',
                ],
            ],
        ];
    }
}
