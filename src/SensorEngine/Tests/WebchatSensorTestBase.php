<?php

namespace OpenDialogAi\Core\SensorEngine\Tests;

use OpenDialogAi\Core\Tests\TestCase;

abstract class WebchatSensorTestBase extends TestCase
{
    /**
     * @param $type
     * @param $data
     * @param null $callbackId
     * @return array
     */
    protected function generateResponseMessage($type, $data, $callbackId = null): array
    {
        $arr = [
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

        if ($callbackId) {
            $arr['content']['callback_id'] = $callbackId;
        }

        return $arr;
    }
}
