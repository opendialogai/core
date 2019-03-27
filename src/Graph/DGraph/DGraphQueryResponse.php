<?php


namespace OpenDialogAi\Core\Graph\DGraph;


use GuzzleHttp\Psr7\Response;

class DGraphQueryResponse
{
    private $response;

    private $data;

    private $extensions;

    public function __construct(Response $response)
    {
        $this->response = $response;

        $responseJson = json_decode($this->response->getBody(), true);

        try {
            $this->data  = $responseJson['data'][DGraphQuery::FUNC_NAME];
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
}
