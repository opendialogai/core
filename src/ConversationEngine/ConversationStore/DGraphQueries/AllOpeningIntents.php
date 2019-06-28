<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;


use Ds\Map;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

class AllOpeningIntents extends DGraphQuery
{
    private $data;

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
        $this->data = $response->getData();
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns all opening intents.
     *
     * @return Map
     */
    public function getIntents()
    {
        $intents = new Map();
        foreach ($this->data as $datum) {
            $conditions = new Map();

            if (isset($datum[Model::HAS_CONDITION])) {
                foreach ($datum[Model::HAS_CONDITION] as $conditionData) {
                    $condition = ConversationQueryFactory::createCondition($conditionData, false);
                    if (isset($condition)) {
                        $conditions->put($condition->getId(), $condition);
                    }
                }
            }

            if (isset($datum[Model::HAS_OPENING_SCENE])) {
                if (isset($datum[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT])) {
                    $matchedIntents = $this->extractIntentsFromParticipant(
                        $datum[MODEL::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT][0]
                    );
                    foreach ($matchedIntents as $intent) {
                        $openingIntent = new OpeningIntent(
                            $intent[Model::ID],
                            $intent[Model::UID],
                            $datum[Model::ID],
                            $datum[Model::UID],
                            $intent[Model::ORDER],
                            isset($intent[Model::CONFIDENCE]) ? $intent[Model::CONFIDENCE] : 1
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

                    if (isset($intent[Model::HAS_INTERPRETER])) {
                        $openingIntent = new OpeningIntent(
                            $intent[Model::ID],
                            $intent[Model::UID],
                            $datum[Model::ID],
                            $datum[Model::UID],
                            $intent[Model::ORDER],
                            isset($intent[Model::CONFIDENCE]) ? $intent[Model::CONFIDENCE] : 1,
                            $intent[Model::HAS_INTERPRETER][0][Model::ID]
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
     * Extract intents from a participant in an opening scene. Looks for says or says_across_scenes relationships
     *
     * @param $participant
     * @return array
     */
    private function extractIntentsFromParticipant($participant)
    {
        if (isset($participant[Model::SAYS])) {
            return $participant[Model::SAYS];
        }

        if (isset($participant[Model::SAYS_ACROSS_SCENES])) {
            return $participant[Model::SAYS_ACROSS_SCENES];
        }

        return [];
    }
}
