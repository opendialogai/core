<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries;


use Ds\Map;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;

class AllOpeningIntents extends DGraphQuery
{
    private $dGraphData;

    private $dGraphClient;

    private $data;

    public function __construct(DGraphClient $client)
    {
        parent::__construct();
        $this->dGraphClient = $client;

        $this->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->setQueryGraph([
                Model::EI_TYPE,
                Model::ID,
                Model::UID,
                Model::HAS_OPENING_SCENE => [
                    MODEL::HAS_USER_PARTICIPANT => [
                        MODEL::SAYS => [
                            MODEL::ID,
                            MODEL::UID,
                            MODEL::HAS_INTERPRETER => [
                                MODEL::ID,
                                MODEL::UID,
                            ]
                        ]
                    ]
                ]
            ]);

        $response = $this->dGraphClient->query($this);
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
            if (isset($datum[Model::HAS_OPENING_SCENE])) {
                if (isset($datum[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT])) {
                    if (isset($datum[MODEL::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT][0][Model::SAYS])) {
                        foreach ($datum[MODEL::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT][0][Model::SAYS] as $intent) {
                            $intents->put(
                                $intent[Model::UID],
                                new OpeningIntent(
                                    $intent[Model::ID],
                                    $intent[Model::UID],
                                    $datum[Model::ID],
                                    $datum[Model::UID]
                                )
                            );
                        }
                        if (isset($intent[Model::HAS_INTERPRETER])) {
                            $intents->put(
                                $intent[Model::UID],
                                new OpeningIntent(
                                    $intent[Model::ID],
                                    $intent[Model::UID],
                                    $datum[Model::ID],
                                    $datum[Model::UID],
                                    $intent[Model::HAS_INTERPRETER][0][Model::ID]
                                )
                            );
                        }
                    }
                }
            }
        }

        return $intents;
    }
}
