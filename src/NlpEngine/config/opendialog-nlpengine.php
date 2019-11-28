<?php

return [

    'available_services' => [
        'microsoft' => \OpenDialogAi\Core\NlpEngine\Service\MsNlpService::class
    ],
    'ms_api_key' => env('MS_COG_SERVICE_KEY'),
    'ms_api_url' => env('MS_COG_SERVICE_URL'),

];
