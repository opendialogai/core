<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

class DGraphQueryResponse
{
    private $data;

    private $extensions;

    public function __construct(Response $response)
    {
        $responseJson = json_decode($response->getBody(), true);

        if (isset($responseJson['errors'])) {
            Log::error('Error while running DGraph query', $responseJson['errors']);
        } else {
            $this->data  = $responseJson['data'][DGraphQuery::FUNC_NAME];
            $this->extensions = $responseJson['extensions'];
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
