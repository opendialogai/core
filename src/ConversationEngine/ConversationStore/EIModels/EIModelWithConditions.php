<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;

use Ds\Set;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\Core\Conversation\Model;

class EIModelWithConditions extends EIModelBase
{
    /* @var Set $conditions */
    private $conditions;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return true;
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param $additionalParameter
     * @return EIModel
     * @throws EIModelCreatorException
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $eiModelCreator = resolve(EIModelCreator::class);

        $conversation = new static();
        $conversation->conditions = new Set();

        if (isset($response[Model::HAS_CONDITION])) {
            foreach ($response[Model::HAS_CONDITION] as $c) {
                $condition = $eiModelCreator->createEIModel(EIModelCondition::class, $c);

                if (isset($condition)) {
                    $conversation->conditions->add($condition);
                }
            }
        }

        return $conversation;
    }

    /**
     * @return Set
     */
    public function getConditions(): Set
    {
        return $this->conditions;
    }

    /**
     * @return bool
     */
    public function hasConditions(): bool
    {
        return !$this->conditions->isEmpty();
    }

    /**
     * @param Set $conditions
     * @return void
     */
    public function setConditions(Set $conditions): void
    {
        $this->conditions = $conditions;
    }
}
