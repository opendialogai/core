<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;

use Countable;
use Ds\Map;
use Ds\Set;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $eiModelCreator = app()->make(EIModelCreator::class);

        $intents = new Map();
        foreach ($response as $conversation) {
            $conditions = new Map();

            if (isset($conversation[Model::HAS_CONDITION])) {
                foreach ($conversation[Model::HAS_CONDITION] as $conditionData) {
                    /* @var EIModelCondition $condition */
                    $condition = $eiModelCreator->createEIModel(EIModelCondition::class, $conditionData);

                    if (isset($condition)) {
                        $conditions->put($condition->getId(), $condition);
                    }
                }
            }

            if (isset($conversation[Model::HAS_OPENING_SCENE])) {
                if (isset($conversation[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT])) {
                    $matchedIntents = self::extractOpeningIntentsFromParticipant(
                        $conversation[Model::HAS_OPENING_SCENE][0][Model::HAS_USER_PARTICIPANT][0]
                    );

                    foreach ($matchedIntents as $intent) {
                        /* @var EIModelIntent $openingIntent */
                        $openingIntent = $eiModelCreator->createEIModel(EIModelIntent::class, $conversation, $intent);

                        $openingIntent->setConditions($conditions);
                        $intents->put($intent[Model::UID], $openingIntent);

                        if (isset($intent[Model::HAS_EXPECTED_ATTRIBUTE])) {
                            foreach ($intent[Model::HAS_EXPECTED_ATTRIBUTE] as $expectedAttribute) {
                                $openingIntent->setExpectedAttribute(
                                    $expectedAttribute[Model::ID],
                                    $expectedAttribute[Model::UID]
                                );
                            }
                        }

                        if (isset($intent[Model::HAS_INPUT_ACTION_ATTRIBUTE])) {
                            foreach ($intent[Model::HAS_INPUT_ACTION_ATTRIBUTE] as $inputActionAttribute) {
                                $openingIntent->setInputActionAttribute(
                                    $inputActionAttribute[Model::ID],
                                    $inputActionAttribute[Model::UID]
                                );
                            }
                        }

                        if (isset($intent[Model::HAS_OUTPUT_ACTION_ATTRIBUTE])) {
                            foreach ($intent[Model::HAS_OUTPUT_ACTION_ATTRIBUTE] as $outputActionAttribute) {
                                $openingIntent->setOutputActionAttribute(
                                    $outputActionAttribute[Model::ID],
                                    $outputActionAttribute[Model::UID]
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
