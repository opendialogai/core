<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use Ds\Map;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Conversation\Model;

/**
 * Client for DGraph using REST API.
 */
class DGraphClient
{
    /** @var Client */
    protected $client;

    const QUERY  = 'query';
    const MUTATE = 'mutate';
    const ALTER  = 'alter';
    const DELETE = 'delete';
    const MUTATE_COMMIT_NOW = 'mutate?commitNow=true';

    const CONDITION = 'Condition';
    const INTENT = 'Intent';
    const PARTICIPANT = 'Participant';
    const SCENE = 'Scene';
    const CONVERSATION = 'Conversation';
    const USER = 'User';
    const USER_ATTRIBUTE = 'UserAttribute';
    const VIRTUAL_INTENT = 'VirtualIntent';

    /** @var string */
    private $schema;

    /**
     * DGraphClient constructor.
     * @param string $dgraphUrl
     * @param string $dGraphPort
     * @param string $schema
     */
    public function __construct(string $dgraphUrl, string $dGraphPort, string $schema)
    {
        $client = new Client([
            'base_uri' => $dgraphUrl . ":" . $dGraphPort
        ]);

        $this->client = $client;
        $this->setSchema($schema);
    }

    /**
     * Test DGraph connection.
     *
     * @return bool
     */
    public function isConnected()
    {
        try {
            $this->client->request('GET', '/');
        } catch (ConnectException $e) {
            return false;
        } catch (GuzzleException $e) {
            Log::error(sprintf('Error connecting to DGraph when trying to test connection- %s', $e->getMessage()));
            return false;
        }

        return true;
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
        $schema = $this->getSchema();
        $outcome = $this->alter($schema);
        return $outcome;
    }

    public function query(DGraphQuery $query)
    {
        $prepared = $query->prepare();
        $this->logMessage(sprintf("Running DGraph query: %s", $prepared));
        $response = $this->client->request(
            'POST',
            self::QUERY,
            [
                'body' => $prepared,
                'headers' => [
                    'Content-Type' => 'application/dql'
                ]
            ]
        );

        return new DGraphQueryResponse($response);
    }

    public function tripleMutation(DGraphMutation $mutation)
    {
        $tripleMutation = $mutation->prepareTripleMutation();
        $this->logMessage(sprintf("Running DGraph triple mutation: %s", $tripleMutation));

        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $tripleMutation,
                'headers' => [
                    'Content-Type' => 'application/rdf'
                ]
            ]
        );
        return new DGraphMutationResponse($response);
    }

    public function jsonMutation(DGraphMutation $mutation)
    {
        $jsonMutation = $mutation->prepareJsonMutation();
        $this->logMessage(sprintf("Running DGraph json mutation: %s", $jsonMutation));

        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $jsonMutation,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $return = (string)$response->getBody();

        return $return;
    }

    public function alter(string $alter)
    {
        $this->logMessage(sprintf("DGraph alter: %s", $alter));

        $response = $this->client->request(
            'POST',
            self::ALTER,
            ['body' => $alter]
        );

        $response = json_decode($response->getBody(), true);

        try {
            return $this->getData($response);
        } catch (DGraphResponseErrorException $e) {
            return "Error processing alter {$e->getMessage()}";
        }
    }

    /**
     * @param $response
     * @return mixed
     * @throws DGraphResponseErrorException
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
            throw new DGraphResponseErrorException($error);
        }

        return $response['data'];
    }

    /**
     * @param $node1Uid
     * @param $node2Uid
     * @param $relationship
     * @return mixed
     * @throws GuzzleException
     */
    public function deleteRelationship($node1Uid, $node2Uid, $relationship)
    {
        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $this->prepareDeleteRelationshipStatement($node1Uid, $node2Uid, $relationship),
                'headers' => [
                    'Content-Type' => 'application/rdf'
                ]
            ]
        );

        $response = json_decode($response->getBody(), true);

        try {
            return $this->getData($response);
        } catch (DGraphResponseErrorException $e) {
            return "Error processing alter {$e->getMessage()}";
        }
    }

    /**
     * @param $startingUid
     * @return mixed
     * @throws GuzzleException
     * @throws DGraphResponseErrorException
     */
    public function deleteConversationAndHistory($startingUid)
    {
        $prepared = $this->prepareDeleteHistoryStatement($startingUid);

        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $prepared,
                'headers' => [
                    'Content-Type' => 'application/rdf'
                ]
            ]
        );

        $response = json_decode($response->getBody(), true);

        return $this->getData($response);
    }

    /**
     * @param $node1Uid
     * @param $node2Uid
     * @param $relationship
     * @param Map|null $facets
     * @return mixed
     * @throws GuzzleException
     */
    public function createRelationship($node1Uid, $node2Uid, $relationship, Map $facets = null)
    {
        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $this->prepareCreateRelationshipStatement($node1Uid, $node2Uid, $relationship, $facets),
                'headers' => [
                    'Content-Type' => 'application/rdf'
                ]
            ]
        );

        $response = json_decode($response->getBody(), true);

        try {
            return $this->getData($response);
        } catch (DGraphResponseErrorException $e) {
            return "Error processing alter {$e->getMessage()}";
        }
    }

    /**
     * @param $node1Uid
     * @param $node2Uid
     * @param $relationship
     * @return string
     */
    private function prepareDeleteRelationshipStatement($node1Uid, $node2Uid, $relationship)
    {
        $statement = sprintf('{ delete { <%s> <%s> <%s> . } }', $node1Uid, $relationship, $node2Uid);
        return $statement;
    }

    /**
     * @param $nodeUid
     * @return string
     */
    private function prepareDeleteNodeStatement($nodeUid)
    {
        $statement = sprintf('{ delete { %s } }', $this->prepareDeleteNodeTriple($nodeUid));
        return $statement;
    }

    private function prepareDeleteNodeTriple(string $nodeUid): string
    {
        return sprintf(' <%s> * * .', $nodeUid);
    }

    /**
     * @param string $startingUid
     * @return string
     */
    private function prepareDeleteHistoryStatement(string $startingUid): string
    {
        // Query and get all history uids in a list
        /** @var DGraphQuery $query */
        $query = (new DGraphQuery())->uid($startingUid)->recurse()->setQueryGraph([
            Model::UID,
            Model::UPDATE_OF
        ]);

        /** @var DGraphQueryResponse $result */
        $result = $this->query($query);

        // Call prepare delete trips for each uid
        /** @var array $uidList */
        $uidList = array_unique($this->historyResultReducer($result->getData()[0], []));

        // Concatenate and return
        /** @var array $triples */
        $triples = array_map([$this, 'prepareDeleteNodeTriple'], $uidList);

        return sprintf('{ delete { %s } }', join("\n", $triples));
    }

    /**
     * @param array $result
     * @param array $carry
     * @return array
     */
    private function historyResultReducer(array $result, array $carry): array
    {
        $carry[] = $result[Model::UID];

        if (array_key_exists(Model::UPDATE_OF, $result)) {
            $carry = array_merge($carry, $this->historyResultReducer($result[Model::UPDATE_OF], $carry));
        }

        return $carry;
    }

    /**
     * @param $node1Uid
     * @param $node2Uid
     * @param $relationship
     * @param Map|null $facets
     * @return string
     */
    private function prepareCreateRelationshipStatement($node1Uid, $node2Uid, $relationship, Map $facets = null)
    {
        $prepared = sprintf('<%s> <%s> <%s>', $node1Uid, $relationship, $node2Uid);

        if (!is_null($facets)) {
            $prepared = sprintf('%s %s', $prepared, DGraphMutation::prepareFacets($facets));
        }

        return sprintf('{ set { %s . } }', $prepared);
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    public function setSchema(string $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Logs a message if the config is set to true
     *
     * @param $message
     */
    private function logMessage($message): void
    {
        if (config("opendialog.core.LOG_DGRAPH_QUERIES")) {
            Log::info("DGRAPH QUERY : " . trim(preg_replace('/\s+/', ' ', $message)));
        }
    }
}
