<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Luis\LuisClient;
use OpenDialogAi\InterpreterEngine\Luis\LuisEntity;
use OpenDialogAi\InterpreterEngine\Luis\LuisRequestFailedException;
use OpenDialogAi\InterpreterEngine\Luis\LuisResponse;

class LuisInterpreter extends BaseInterpreter
{
    const ATTRIBUTE_NAMESPACE = 'attribute.luis.';

    protected static $name = 'interpreter.core.luis';

    /** @var LuisClient */
    private $client;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->client = app()->make(LuisClient::class);
    }

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            $luisResponse = $this->client->queryLuis($utterance->getText());
            $intent = $this->createOdIntent($luisResponse);
        } catch (LuisRequestFailedException $e) {
            Log::warning(sprintf("Luis interpreter failed at a LUIS client level with message %s", $e->getMessage()));
            $intent = new NoMatchIntent();
        } catch (FieldNotSupported $e) {
            Log::warning("Trying to use LUIS interpreter to interpret an utterance that does not support text ");
            $intent = new NoMatchIntent();
        }

        return [$intent];
    }

    /**
     * Creates an @see Intent from the LUIS response. If there is no intent in th response, a default NO_MATCH intent is
     * returned
     *
     * @param LuisResponse $response
     * @return NoMatchIntent|Intent
     */
    private function createOdIntent(LuisResponse $response)
    {
        $intent = new NoMatchIntent();

        if ($topIntent = $response->getTopScoringIntent()) {
            $intent = Intent::createIntentWithConfidence($topIntent->getLabel(), $topIntent->getConfidence());
        }

        foreach ($this->extractAttributes($response->getEntities()) as $attribute) {
            $intent->addAttribute($attribute);
        }

        return $intent;
    }

    /**
     * @param LuisEntity[] $luisEntities
     * @return Map
     */
    private function extractAttributes(array $luisEntities): Map
    {
        $attributes = new AttributeBag();

        foreach ($luisEntities as $entity) {
            $attributes->addAttribute($this->resolveLuisEntity($entity));
        }

        return $attributes->getAttributes();
    }

    /**
     * Tries to resolve the LUIS entity type with any registered in config. If there is not an entry for the entity, a
     * @see StringAttribute is used.
     *
     * Any returned entities are given the name attribute.luis.{entity_type}
     *
     * @param LuisEntity $entity
     * @return AttributeInterface
     */
    private function resolveLuisEntity(LuisEntity $entity)
    {
        /** @var AbstractAttribute[] $luisEntities */
        $luisEntities = config('opendialog.interpreter_engine.luis_entities');

        if (isset($luisEntities[$entity->getType()])) {
            $attribute = $luisEntities[$entity->getType()];
        } else {
            $attribute = StringAttribute::class;
        }

        return new $attribute(self::ATTRIBUTE_NAMESPACE . $entity->getType(), $entity->getEntityString());
    }
}
