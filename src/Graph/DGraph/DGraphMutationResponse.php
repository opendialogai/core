<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use GuzzleHttp\Psr7\Response;

class DGraphMutationResponse
{
    const SUCCESS = 'Success';

    private $response;

    private $data = null;

    private $errors;

    private $extensions;

    public function __construct(Response $response)
    {
        $this->response = $response;

        $responseJson = json_decode($this->response->getBody(), true);

        if (isset($responseJson['data'])) {
            $this->data = $responseJson['data'];
        }

        if (isset($responseJson['errors'])) {
            $this->errors = $responseJson['errors'];
        }

        if (isset($responseJson['extensions'])) {
            $this->extensions = $responseJson['extensions'];
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function isSuccessful()
    {
        if ($this->getData() && $this->getData()['code'] == self::SUCCESS) {
            return true;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
