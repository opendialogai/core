<?php


namespace OpenDialogAi\Core\Graph\DGraph;

use GuzzleHttp\Client;

/**
 * Client for DGraph using REST API.
 */
class DGraphClient
{
    /** @var \GuzzleHttp\Client */
    protected $client;

    /** @var string */
    protected $dGraphQueries;

    const QUERY  = 'query';
    const MUTATE = 'mutate';
    const ALTER  = 'alter';

    public function __construct($dgraphUrl, $dGraphPort)
    {
        $client = new Client([
            'base_uri' => $dgraphUrl . ":" . $dGraphPort
        ]);

        $this->client = $client;

        $this->dGraphQueriesFilePath = '';
    }

    /**
     * Drops the current schema and all data
     */
    public function dropSchema()
    {
        return $this->alter('{"drop_all": true}');
    }

    /**
     * Imports the schema definition file and runs the alter command
     */
    public function initSchema()
    {
        $schema = $this->getCurrentSchema();
        $outcome = $this->alter($schema);
        return $outcome;
    }

    public function query(DGraphQuery $query)
    {
        $response = $this->client->request(
            'POST',
            self::QUERY,
            ['body' => $query->prepare()]
        );
        return new DGraphQueryResponse($response);
    }

    public function tripleMutation(DGraphMutation $mutation)
    {
        $response = $this->client->request(
            'POST',
            self::MUTATE,
            [
                'body' => $mutation->prepareTripleMutation(),
                'headers' => [
                    'X-Dgraph-CommitNow' => 'true',
                ]
            ]
        );

        return new DGraphMutationResponse($response);
    }

    public function jsonMutation(DGraphMutation $mutation)
    {
        $response = $this->client->request(
            'POST',
            self::MUTATE,
            [
                'body' => $mutation->prepareJsonMutation(),
                'headers' => [
                    'X-Dgraph-CommitNow' => 'true',
                    'X-Dgraph-MutationType' => 'json'
                ]
            ]
        );

        $return = (string)$response->getBody();

        return $return;
    }

    public function alter(string $alter)
    {
        $response = $this->client->request(
            'POST',
            self::ALTER,
            ['body' => $alter]
        );

        $response = json_decode($response->getBody(), true);

        try {
            return $this->getData($response);
        } catch (\Exception $e) {
            return "Error processing alter {$e->getMessage()}";
        }
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    private function getData($response)
    {
        $error = null;
        if (!is_array($response)) {
            $error = "Response should be an array";
        }

        if (!isset($response['data'])) {
            $error = "Response should have data";
        }

        if ($error) {
            throw new \Exception($error);
        }

        return $response['data'];
    }
}
