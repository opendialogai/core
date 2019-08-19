<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;


use Ds\Map;
use Ds\Set;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

class AllOpeningIntents extends DGraphQuery
{
    private $conversations;

    public function __construct(DGraphClient $client)
    {
        parent::__construct();
        $this->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
                Model::EI_TYPE,
                Model::ID,
                Model::UID,
                Model::HAS_CONDITION => ConversationQueryFactory::getConditionGraph(),
                Model::HAS_OPENING_SCENE => [
                    Model::HAS_USER_PARTICIPANT => [
                        Model::SAYS => [
                            Model::ID,
                            Model::UID,
                            Model::ORDER,
                            Model::CONFIDENCE,
                            Model::CAUSES_ACTION => [
                                Model::UID,
                                Model::ID
                            ],
                            Model::HAS_INTERPRETER => [
                                Model::ID,
                                Model::UID,
                            ],
                            Model::HAS_EXPECTED_ATTRIBUTE => [
                                Model::ID,
                                Model::UID
                            ]
                        ],
                        Model::SAYS_ACROSS_SCENES => [
                            Model::ID,
                            Model::UID,
                            Model::ORDER,
                            Model::CONFIDENCE,
                            Model::CAUSES_ACTION => [
                                Model::UID,
                                Model::ID
                            ],
                            Model::HAS_INTERPRETER => [
                                Model::ID,
                                Model::UID,
                            ],
                            Model::HAS_EXPECTED_ATTRIBUTE => [
                                Model::ID,
                                Model::UID
                            ]
                        ]
                    ],
                ]
            ]);

        $response = $client->query($this);
        $this->conversations = $response->getData();
    }

    public function getData()
    {
        return $this->conversations;
    }

    /**
     * Returns all opening intents.
     *
     * @return Map
     */
    public function getIntents()
    {
        $intents = new Map();
        foreach ($this->conversations as $conversation) {
            $conditions = new Map();

            if (isset($conversation[Model::HAS_CONDITION])) {
                foreach ($conversation[Model::HAS_CONDITION] as $conditionData) {
                    $condition = ConversationQueryFactory::createCondition($conditionData, false);
                    if (isset($condition)) {
                        $conditions->put($condition->getId(), $condition);
                    }
                }
            }

            if (isset($conversation[Model::HAS_OPENING_SCENE])) {
                if (isset($conversation[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT])) {
                    $matchedIntents = $this->extractOpeningIntentsFromParticipant(
                        $conversation[MODEL::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT][0]
                    );
                    foreach ($matchedIntents as $intent) {
                        $openingIntent = new OpeningIntent(
                            $intent[Model::ID],
                            $intent[Model::UID],
                            $conversation[Model::ID],
                            $conversation[Model::UID],
                            $intent[Model::ORDER],
                            isset($intent[Model::CONFIDENCE]) ? $intent[Model::CONFIDENCE] : 1,
                            isset($intent[Model::HAS_INTERPRETER]) ? $intent[Model::HAS_INTERPRETER][0][Model::ID] : null
                        );
                        $openingIntent->setConditions($conditions);
                        $intents->put(
                            $intent[Model::UID],
                            $openingIntent
                        );

                        if (isset($intent[Model::HAS_EXPECTED_ATTRIBUTE])) {
                            foreach ($intent[Model::HAS_EXPECTED_ATTRIBUTE] as $expectedAttribute) {
                                $openingIntent->addExpectedAttribute($expectedAttribute['id']);
                            }
                        }
                    }
                }
            }
        }

        return $intents;
    }

    /**
     * Extract opening intents from a participant in an opening scene. Looks for says and says_across_scenes relationships
     *
     * @param $participant
     * @return Set
     */
    private function extractOpeningIntentsFromParticipant($participant): Set
    {
        $intents = $this->extractAllIntentsFromParticipant($participant);

        $previousKeptIntent = null;

        // Sort the intents by order and then filter them so that we get just the first user intent(s)
        $intents = $intents->sorted(
            function ($a, $b) {
                return $a[Model::ORDER] > $b[Model::ORDER];
            }
        )->filter(
            function ($possibleIntent) use (&$previousKeptIntent) {
                // Intents are considered sequential if its the first or if it directly follows the previously kept intent
                $intentsAreSequential = is_null($previousKeptIntent)
                    || $previousKeptIntent[Model::ORDER] + 1 == $possibleIntent[Model::ORDER];

                $shouldKeep = $possibleIntent[Model::ORDER] > 0 && $intentsAreSequential;

                if ($shouldKeep) {
                    $previousKeptIntent = $possibleIntent;
                }

                return $shouldKeep;
            }
        );

        return $intents;
    }

    /**
     * @param $participant
     * @return Set
     */
    private function extractAllIntentsFromParticipant($participant): Set
    {
        $intents = new Set();

        if (isset($participant[Model::SAYS])) {
            $intents->add(...$participant[Model::SAYS]);
        }

        if (isset($participant[Model::SAYS_ACROSS_SCENES])) {
            $intents->add(...$participant[Model::SAYS_ACROSS_SCENES]);
        }

        return $intents;
    }
}
