<?php

namespace OpenDialogAi\Core\NlpEngine\Client;

use GuzzleHttp\Client;

class MsClient extends Client
{
    /**
     * MsClient constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config['base_uri'] = config('opendialog.nlp_engine.ms_api_url');
        $config['headers'] = [
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => config('opendialog.nlp_engine.ms_api_key')
        ];
        parent::__construct($config);
    }
}
