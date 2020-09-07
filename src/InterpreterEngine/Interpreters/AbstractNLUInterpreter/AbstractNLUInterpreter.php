<?php

namespace OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\AttributeBag\AttributeBag;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
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
    public function interpret(UtteranceInterface $utterance): array
    {
        try {
            $clientResponse = $this->client->query($utterance->getText());
            $intent = $this->createOdIntent($clientResponse);
        } catch (AbstractNLURequestFailedException $e) {
            Log::warning(sprintf("%s failed with message: %s", static::$name, $e->getMessage()));
            $intent = new NoMatchIntent();
        }

        return [$intent];
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
        $intent = new NoMatchIntent();

        if ($topIntent = $response->getTopScoringIntent()) {
            Log::debug(
                sprintf(
                    'Creating intent from %s with name %s and %.2f confidence.',
                    static::$name,
                    $topIntent->getLabel(),
                    $topIntent->getConfidence()
                )
            );
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
     * Tries to resolve the entity type with any registered in config. If there is not an entry for the entity, a
     * @see StringAttribute is used.
     *
     * @param AbstractNLUEntity $entity
     * @return AttributeInterface
     */
    protected function resolveEntity(AbstractNLUEntity $entity): AttributeInterface
    {
        /** @var AbstractAttribute[] $entityList */
        $entityList = config($this->getEntityConfigKey());

        $attributeName = $entity->getType();
        // If we have bound the entity name to an attribute name, use that instead
        if (isset($entityList[$entity->getType()])) {
            $attributeName = $entityList[$entity->getType()];
        }

        try {
            return AttributeResolver::getAttributeFor($attributeName, $entity->getResolutionValues()[0]);
        } catch (AttributeIsNotSupported $e) {
            Log::warning(
                sprintf(
                    "Unsupported attribute type %s returned from %s - using StringAttribute",
                    $attributeName,
                    static::$name
                )
            );

            return AttributeResolver::getAttributeFor($attributeName, $entity->getResolutionValues()[0]);
        }
    }

    /**
     * @param AbstractNLUEntity[] $entities
     * @return Map
     */
    protected function extractAttributes(array $entities): Map
    {
        $attributes = new AttributeBag();

        foreach ($entities as $entity) {
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
