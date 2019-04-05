<?php


namespace OpenDialogAi\Core\Graph\DGraph;


use GuzzleHttp\Psr7\Response;

class DGraphMutationResponse
{
    const SUCCESS = 'Success';

    private $response;

    private $data;

    private $extensions;

    public function __construct(Response $response)
    {
        $this->response = $response;

        $responseJson = json_decode($this->response->getBody(), true);

        try {
            $this->data  = $responseJson['data'];
        } catch (\Exception $e) {
            return "Error processing query - {$e->getMessage()}";
        }

        $this->extensions = $responseJson['extensions'];
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
        if ($this->getData()['code'] == self::SUCCESS) {
            return true;
        }

        return false;
    }
}
