<?php

namespace OpenDialogAi\Core\Graph\DGraph;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Conversation\Model;

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

    const CONDITION = 'Condition';
    const INTENT = 'Intent';
    const PARTICIPANT = 'Participant';
    const SCENE = 'Scene';
    const CONVERSATION = 'Conversation';
    const USER = 'User';
    const USER_ATTRIBUTE = 'UserAttribute';


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
    private function schema()
    {
        return "
            <attributes>: string .
            <causes_action>: [uid] .
            <context>: string .
            <conversation_status>: string @index(exact) .
            <conversation_version>: int .
            <core.attribute.completes>: default .
            <core.attribute.order>: default .
            <ei_type>: string @index(exact) .
            <has_bot_participant>: [uid] @reverse .
            <has_condition>: [uid] .
            <has_interpreter>: [uid] .
            <has_opening_scene>: [uid] @reverse .
            <has_scene>: [uid] .
            <has_user_participant>: [uid] @reverse .
            <has_attribute>: [uid] .
            <id>: string @index(exact) .
            <instance_of>: uid @reverse .
            <update_of>: uid @reverse .
            <listens_for>: [uid] @reverse .
            <name>: default .
            <operation>: string .
            <parameters>: string .     
            <says>: [uid] @reverse .
            <having_conversation>: [uid] @reverse .
            <says_across_scenes>: [uid] @reverse .
            <listens_for_across_scenes>: [uid] @reverse .
            <user_attribute_type>: string .
            <user_attribute_value>: string .
                        
            type " . self::CONDITION . " {
                attributes: string
                context: string
                ei_type: string
                id: string
                operation: string
                parameters: string
            }
            type " . self::INTENT . " {
                causes_action: [uid]
                core.attribute.completes: default
                core.attribute.order: default
                ei_type: string
                has_condition: [uid]
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
                has_condition: [uid]
                has_user_participant: [uid]
            }
            type " . self::CONVERSATION . " {
                conversation_status: string
                conversation_version: int
                ei_type: string
                has_condition: [uid]
                has_opening_scene: [uid]
                has_scene: [uid]
                id: string
                instance_of: uid
                update_of: uid
            }
            type " . self::USER_ATTRIBUTE . " {
                id: string
                ei_type: string
                user_attribute_type: string
                user_attribute_value: string
            }
            type " . self::USER . " {
                id: string
                ei_type: string
                has_attribute: [uid]
            }
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
