<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;


use Ds\Map;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

class AllOpeningIntents extends DGraphQuery
{
    /**
     * @var array
     */
    private $conversations;

    public function __construct(DGraphClient $client)
    {
        parent::__construct();
        $this->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
                Model::EI_TYPE,
                Model::ID,
                Model::UID,
                Model::HAS_CONDITION => DGraphConversationQueryFactory::getConditionGraph(),
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
        $this->setConversations($response->getData());
    }

    private function setConversations($conversations): void
    {
        if (is_null($conversations) || count($conversations) < 1) {
            $this->conversations = [];
        } else {
            $this->conversations = $conversations;
        }
    }

    /**
     * @return array
     */
    public function getConversations(): array
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
        foreach ($this->getConversations() as $conversation) {
            $conditions = new Map();

            if (isset($conversation[Model::HAS_CONDITION])) {
                foreach ($conversation[Model::HAS_CONDITION] as $conditionData) {
                    $condition = DGraphConversationQueryFactory::createCondition($conditionData, false);
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
}
