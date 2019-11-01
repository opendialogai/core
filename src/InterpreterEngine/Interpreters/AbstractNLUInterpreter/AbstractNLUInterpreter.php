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
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\InterpreterEngine\BaseInterpreter;
use OpenDialogAi\InterpreterEngine\Interpreters\NoMatchIntent;
use OpenDialogAi\InterpreterEngine\Luis\AbstractNLUClient;
use OpenDialogAi\InterpreterEngine\Luis\AbstractNLURequestFailedException;
use OpenDialogAi\InterpreterEngine\Luis\LuisEntity;

abstract class AbstractNLUInterpreter extends BaseInterpreter
{
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
            Log::warning(sprintf("%s failed at a client level with message: %s", static::$name, $e->getMessage()));
            $intent = new NoMatchIntent();
        } catch (FieldNotSupported $e) {
            Log::warning(sprintf("Trying to use %s to interpret an utterance that does not support text", static::$name));
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
            Log::debug(sprintf('Creating intent from %s with name %s', static::$name, $topIntent->getLabel()));
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
     * @param AbstractNLUEntity $entity
     * @return AttributeInterface
     * @see StringAttribute is used.
     *
     */
    protected function resolveEntity(AbstractNLUEntity $entity): AttributeInterface
    {
        /** @var AbstractAttribute[] $entityList */
        $entityList = config('opendialog.interpreter_engine.luis_entities');

        $attributeName = $entity->getType();
        // If we have bound the LUIS entity name to an attribute name, use that instead
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

            return new StringAttribute($attributeName, $entity->getResolutionValues()[0]);
        }
    }

    /**
     * @param LuisEntity[] $luisEntities
     * @return Map
     */
    protected function extractAttributes(array $luisEntities): Map
    {
        $attributes = new AttributeBag();

        foreach ($luisEntities as $entity) {
            $attributes->addAttribute($this->resolveLuisEntity($entity));
        }

        return $attributes->getAttributes();
    }
}
