<?php

namespace OpenDialogAi\Core\SensorEngine\tests;

use OpenDialogAi\Core\Tests\TestCase;

Abstract class WebchatSensorTestBase extends TestCase
{
    /**
     * @param $type
     * @param $data
     * @param null $callbackId
     * @return array
     */
    protected function generateResponseMessage($type, $data, $callbackId = null): array
    {
        return [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => $type,
                'data' => $data,
                'callback_id' => $callbackId,
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
