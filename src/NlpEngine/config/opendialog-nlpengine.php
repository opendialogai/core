<?php

use OpenDialogAi\Core\NlpEngine\Providers\MsNlpProvider;

return [
    'available_nlp_providers' => [
        MsNlpProvider::class
    ],

    'ms_api_key' => env('MS_COG_SERVICE_KEY'),
    'ms_api_url' => env('MS_COG_SERVICE_URL')
];
