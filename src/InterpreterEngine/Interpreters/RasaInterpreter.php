<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Rasa\RasaClient;
use OpenDialogAi\InterpreterEngine\Rasa\RasaEntity;
use OpenDialogAi\InterpreterEngine\Rasa\RasaRequestFailedException;
use OpenDialogAi\InterpreterEngine\Rasa\RasaResponse;

class RasaInterpreter extends BaseInterpreter
{
    protected static $name = 'interpreter.core.rasa';

    /** @var RasaClient */
    private $client;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
        $this->client = app()->make(RasaClient::class);
    }

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            $rasaResponse = $this->client->queryRasa($utterance->getText());
            $intent = $this->createOdIntent($rasaResponse);
        } catch (RasaRequestFailedException $e) {
            Log::warning(sprintf("Rasa interpreter failed at a RASA client level with message %s", $e->getMessage()));
            $intent = new NoMatchIntent();
        } catch (FieldNotSupported $e) {
            Log::warning("Trying to use RASA interpreter to interpret an utterance that does not support text ");
            $intent = new NoMatchIntent();
        }

        return [$intent];
    }

    /**
     * Creates an @see Intent from the RASA response. If there is no intent in the response, a default NO_MATCH intent
     * is returned
     *
     * @param RasaResponse $response
     * @return NoMatchIntent|Intent
     */
    private function createOdIntent(RasaResponse $response)
    {
        $intent = new NoMatchIntent();

        if ($topIntent = $response->getTopScoringIntent()) {
            Log::debug(sprintf('Creating intent from Rasa with name %s', $topIntent->getLabel()));
            $intent = Intent::createIntentWithConfidence($topIntent->getLabel(), $topIntent->getConfidence());
        }

        /* @var AttributeInterface $attribute */
        foreach ($this->extractAttributes($response->getEntities()) as $attribute) {
            Log::debug(sprintf('Adding attribute %s to intent.', $attribute->getId()));
            $intent->addAttribute($attribute);
        }

        return $intent;
    }

    /**
     * @param RasaEntity[] $rasaEntities
     * @return Map
     */
    private function extractAttributes(array $rasaEntities): Map
    {
        $attributes = new AttributeBag();

        foreach ($rasaEntities as $entity) {
            $attributes->addAttribute($this->resolveRasaEntity($entity));
        }

        return $attributes->getAttributes();
    }

    /**
     * Tries to resolve the RASA entity type with any registered in config. If there is not an entry for the entity, a
     * @see StringAttribute is used.
     *
     * Any returned entities are given the name attribute.rasa.{entity_type}
     *
     * @param RasaEntity $entity
     * @return AttributeInterface
     */
    private function resolveRasaEntity(RasaEntity $entity)
    {
        /** @var AbstractAttribute[] $rasaEntities */
        $rasaEntities = config('opendialog.interpreter_engine.rasa_entities');

        $attributeName = $entity->getType();
        // If we have bound the RASA entity name to an attribute name, use that instead
        if (isset($rasaEntities[$entity->getType()])) {
            $attributeName = $rasaEntities[$entity->getType()];
        }

        try {
            return AttributeResolver::getAttributeFor($attributeName, $entity->getResolutionValues()[0]);
        } catch (AttributeIsNotSupported $e) {
            Log::warning(
                sprintf(
                    "Unsupported attribute type %s returned from RASA - using StringAttribute",
                    $attributeName
                )
            );

            return new StringAttribute($attributeName, $entity->getResolutionValues()[0]);
        }
    }
}
