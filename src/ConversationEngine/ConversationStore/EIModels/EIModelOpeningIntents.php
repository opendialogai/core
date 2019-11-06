<?php


namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;


use Countable;
use Ds\Map;
use Ds\Set;
use Illuminate\Contracts\Container\BindingResolutionException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\Core\Conversation\Model;

class EIModelOpeningIntents extends EIModelBase implements Countable
{
    private $intents;

    public function __construct(Map $intents)
    {
        $this->intents = $intents;
    }

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param null $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return array_reduce($response, function ($carry, $item) {
            return $carry && EIModelBase::hasEIType($item, Model::CONVERSATION_TEMPLATE);
        }, true);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array|array $response
     * @param null $additionalParameter
     * @return EIModel
     * @throws EIModelCreatorException
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        try {
            $eiModelCreator = app()->make(EIModelCreator::class);
        } catch (BindingResolutionException $e) {
            throw new EIModelCreatorException($e->getMessage());
        }

        $intents = new Map();
        foreach ($response as $conversation) {
            $conversationConditions = self::createConditions($conversation, $eiModelCreator);

            if (isset($conversation[Model::HAS_OPENING_SCENE])) {
                if (isset($conversation[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT])) {
                    $matchedIntents = self::extractOpeningIntentsFromParticipant(
                        $conversation[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT][0]
                    );

                    foreach ($matchedIntents as $intent) {
                        /* @var EIModelIntent $openingIntent */
                        $openingIntent = $eiModelCreator->createEIModel(EIModelIntent::class, $conversation, $intent);

                        $allConditions = $conversationConditions;
                        self::collectSceneIntents($openingIntent, $intent, $eiModelCreator);
                        $allConditions = $allConditions->merge($openingIntent->getConditions());
                        $openingIntent->setConditions($allConditions);

                        $intents->put($intent[Model::UID], $openingIntent);

                        if (isset($intent[Model::HAS_EXPECTED_ATTRIBUTE])) {
                            foreach ($intent[Model::HAS_EXPECTED_ATTRIBUTE] as $expectedAttribute) {
                                $openingIntent->setExpectedAttribute(
                                    $expectedAttribute[Model::ID],
                                    $expectedAttribute[Model::UID]
                                );
                            }
                        }
                    }
                }
            }
        }

        return new self($intents);
    }

    /**
     * Extract opening intents from a participant in an opening scene. Looks for says and says_across_scenes relationships
     *
     * @param $participant
     * @return Set
     */
    private static function extractOpeningIntentsFromParticipant($participant): Set
    {
        $intents = self::extractAllIntentsFromParticipant($participant);

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
    private static function extractAllIntentsFromParticipant($participant): Set
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

    /**
     * @param EIModelIntent $openingIntent
     * @param array $intent
     * @param EIModelCreator $eiModelCreator
     * @throws EIModelCreatorException
     */
    private static function collectSceneIntents(EIModelIntent $openingIntent, array $intent, EIModelCreator $eiModelCreator): void
    {
        if (key_exists(Model::LISTENED_BY_FROM_SCENES, $intent)) {
            $participant = $intent[Model::LISTENED_BY_FROM_SCENES][0];

            if (key_exists(Model::BOT_PARTICIPATES_IN, $participant)) {
                $scene = $participant[Model::BOT_PARTICIPATES_IN][0];
            } else if (key_exists(Model::USER_PARTICIPATES_IN, $participant)) {
                $scene = $participant[Model::USER_PARTICIPATES_IN][0];
            } else {
                return;
            }

            $conditions = self::createConditions($scene, $eiModelCreator);

            foreach ($conditions as $condition) {
                $openingIntent->addCondition($condition);
            }
        }
    }

    /**
     * @param array $itemWithConditions
     * @param EIModelCreator $eiModelCreator
     * @return Set
     * @throws EIModelCreatorException
     */
    private static function createConditions(array $itemWithConditions, EIModelCreator $eiModelCreator): Set
    {
        $conditions = new Set();

        if (isset($itemWithConditions[Model::HAS_CONDITION])) {
            foreach ($itemWithConditions[Model::HAS_CONDITION] as $conditionData) {
                /* @var EIModelCondition $condition */
                $condition = $eiModelCreator->createEIModel(EIModelCondition::class, $conditionData);

                if (isset($condition)) {
                    $conditions->add($condition);
                }
            }
        }

        return $conditions;
    }

    /**
     * @return Map
     */
    public function getIntents(): Map
    {
        return $this->intents;
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->getIntents()->count();
    }
}
