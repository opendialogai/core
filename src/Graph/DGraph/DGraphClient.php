<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Client for DGraph using REST API.
 */
class DGraphClient
{
    /** @var Client */
    protected $client;

    /** @var string */
    protected $dGraphQueries;

    const QUERY  = 'query';
    const MUTATE = 'mutate';
    const ALTER  = 'alter';
    const DELETE = 'delete';
    const MUTATE_COMMIT_NOW = 'mutate?commitNow=true';

    const INTENT = 'Intent';
    const PARTICIPANT = 'Participant';
    const SCENE = 'Scene';
    const CONVERSATION = 'Conversation';
    const USER = 'User';


    public function __construct($dgraphUrl, $dGraphPort)
    {
        $client = new Client([
            'base_uri' => $dgraphUrl . ":" . $dGraphPort
        ]);

        $this->client = $client;
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
        $schema = $this->schema();
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
                    'Content-Type' => 'application/graphql+-'
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
        } catch (\Exception $e) {
            return "Error processing alter {$e->getMessage()}";
        }
    }

    public function deleteNode($nodeUid)
    {
        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $this->prepareDeleteNodeStatement($nodeUid),
                'headers' => [
                    'Content-Type' => 'application/rdf'
                ]
            ]
        );

        $response = json_decode($response->getBody(), true);

        try {
            return $this->getData($response);
        } catch (\Exception $e) {
            return "Error processing alter {$e->getMessage()}";
        }
    }

    /**
     * @param $node1Uid
     * @param $node2Uid
     * @param $relationship
     * @return mixed
     * @throws GuzzleException
     */
    public function createRelationship($node1Uid, $node2Uid, $relationship)
    {
        $response = $this->client->request(
            'POST',
            self::MUTATE_COMMIT_NOW,
            [
                'body' => $this->prepareCreateRelationshipStatement($node1Uid, $node2Uid, $relationship),
                'headers' => [
                    'Content-Type' => 'application/rdf'
                ]
            ]
        );

        $response = json_decode($response->getBody(), true);

        try {
            return $this->getData($response);
        } catch (\Exception $e) {
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
        $statement = sprintf('{ delete { <%s> * * . } }', $nodeUid);
        return $statement;
    }

    /**
     * @param $node1Uid
     * @param $node2Uid
     * @param $relationship
     * @return string
     */
    private function prepareCreateRelationshipStatement($node1Uid, $node2Uid, $relationship)
    {
        $statement = sprintf('{ set { <%s> <%s> <%s> . } }', $node1Uid, $relationship, $node2Uid);
        return $statement;
    }

    /**
     * @return string
     */
    private function prepareUserAttributes()
    {
        $userAttributes = '';
        foreach (config('opendialog.context_engine.supported_attributes') as $name => $type) {
            $userAttributes .= "{$name}: default\n";
        }

        return $userAttributes;
    }

    /**
     * @return string
     */
    private function schema()
    {
        $userAttributes = $this->prepareUserAttributes();

        return "
            <causes_action>: [uid] .
            <conversation_status>: string .
            <conversation_version>: int .
            <core.attribute.completes>: default .
            <core.attribute.order>: default .
            <ei_type>: string @index(exact) .
            <has_bot_participant>: [uid] @reverse .
            <has_interpreter>: [uid] .
            <has_opening_scene>: [uid] @reverse .
            <has_scene>: [uid] .
            <has_user_participant>: [uid] @reverse .
            <id>: string @index(exact) .
            <instance_of>: uid @reverse .
            <update_of>: uid @reverse .
            <listens_for>: [uid] @reverse .
            <name>: default .
            <says>: [uid] @reverse .
            <having_conversation>: [uid] @reverse .
            <says_across_scenes>: [uid] @reverse .
            <listens_for_across_scenes>: [uid] @reverse .
            type " . self::INTENT . " {
                causes_action: [uid]
                core.attribute.completes: default
                core.attribute.order: default
                ei_type: string
                has_interpreter: [uid]
                id: string
            }
            type " . self::PARTICIPANT . " {
                ei_type: string
                id: string
                listens_for: [uid]
                says: [uid]
                says_across_scenes: [uid]
                listens_for_across_scenes: [uid]
            }
            type " . self::SCENE . " {
                ei_type: string
                id: string
                has_bot_participant: [uid]
                has_user_participant: [uid]
            }
            type " . self::CONVERSATION . " {
                conversation_status: string
                conversation_version: int
                ei_type: string
                has_opening_scene: [uid]
                has_scene: [uid]
                id: string
                instance_of: uid
                update_of: uid
            }
            type " . self::USER . " {{$userAttributes}}
        ";
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
