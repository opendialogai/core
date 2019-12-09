<?php

return [

    'available_services' => [
        'microsoft' => \OpenDialogAi\Core\NlpEngine\Service\MsNlpService::class
    ],
    'ms_api_key' => env('MS_COG_SERVICE_KEY'),
    'ms_api_url' => env('MS_COG_SERVICE_URL'),
    'default_language' => env('NLP_LANGUAGE'),
    'default_country_code' => env('NLP_COUNTRY_CODE')
];
