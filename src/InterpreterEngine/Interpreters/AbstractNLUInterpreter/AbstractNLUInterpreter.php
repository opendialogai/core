<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;

abstract class AbstractNLUInterpreter extends BaseInterpreter
{
    protected static $entityConfigKey = "";

    /** @var AbstractNLUClient */
    protected $client;

    /**
     * @inheritdoc
     */
    public function interpret(UtteranceAttribute $utterance): IntentCollection
    {
        try {
            $clientResponse = $this->client->query($utterance->getText());
            $intent = $this->createOdIntent($clientResponse);
        } catch (AbstractNLURequestFailedException $e) {
            Log::warning(sprintf("%s failed with message: %s", static::$name, $e->getMessage()));
            $intent = new Intent();
            $intent->setODId('intent.core.NoMatch');
        }
        $collection = new IntentCollection();
        $collection->add($intent);
        return $collection;
    }

    /**
     * Creates an @see Intent from the client response. If there is no intent in the response, a default NO_MATCH intent
     * is returned
     *
     * @param AbstractNLUResponse $response
     * @return Intent
     */
    protected function createOdIntent(AbstractNLUResponse $response): Intent
    {
        $intent = new Intent();
        $intent->setODId('intent.core.NoMatch');

        if ($topIntent = $response->getTopScoringIntent()) {
            Log::debug(
                sprintf(
                    'Creating intent from %s with name %s and %.2f confidence.',
                    static::$name,
                    $topIntent->getLabel(),
                    $topIntent->getConfidence()
                )
            );
            $intent = new Intent();
            $intent->setODId($topIntent->getLabel());
            $intent->setConfidence($topIntent->getConfidence());
        }

        /* @var Attribute $attribute */
        foreach ($this->extractAttributes($response->getEntities()) as $attribute) {
            Log::debug(sprintf('Adding attribute %s to intent.', $attribute->getId()));
            $intent->addAttribute($attribute);
        }

        return $intent;
    }

    /**
     * Tries to resolve the entity type with any registered in config. If there is not an entry for the entity, a
     * @param AbstractNLUEntity $entity
     * @return \OpenDialogAi\AttributeEngine\Contracts\Attribute
     * @see StringAttribute is used.
     *
     */
    protected function resolveEntity(AbstractNLUEntity $entity): Attribute
    {
        /** @var \OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute[] $entityList */
        $entityList = config($this->getEntityConfigKey());

        $attributeName = $entity->getType();
        // If we have bound the entity name to an attribute name, use that instead
        if (isset($entityList[$entity->getType()])) {
            $attributeName = $entityList[$entity->getType()];
        }

        return AttributeResolver::getAttributeFor($attributeName, $entity->getResolutionValues()[0]);
    }

    /**
     * @param AbstractNLUEntity[] $luisEntities
     * @return Map
     */
    protected function extractAttributes(array $luisEntities): Map
    {
        $attributes = new BasicAttributeBag();

        foreach ($luisEntities as $entity) {
            $attributes->addAttribute($this->resolveEntity($entity));
        }

        return $attributes->getAttributes();
    }

    /**
     * @return string
     * @throws AbstractNLURequestFailedException
     */
    protected function getEntityConfigKey(): string
    {
        if (static::$entityConfigKey == "") {
            throw new AbstractNLURequestFailedException(
                sprintf("Entity config key was not set for %s.", static::$name)
            );
        }

        return static::$entityConfigKey;
    }
}
