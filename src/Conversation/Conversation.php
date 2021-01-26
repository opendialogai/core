<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

/**
 * A Conversation is a collection of Scenes.
 */
class Conversation extends NodeWithConditions
{
    const SAVED = 'saved';
    const ACTIVATABLE = 'activatable';
    const ACTIVATED = 'activated';
    const DEACTIVATED = 'deactivated';
    const ARCHIVED = 'archived';

    /** @var Map */
    private $allScenes;

    /** @var Map */
    private $allIntents;

    public function __construct($id, $conversationStatus, $conversationVersion)
    {
        parent::__construct($id);
        $this->setGraphType(DGraphClient::CONVERSATION);

        $this->addAttribute(AttributeResolver::getAttributeFor(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE));
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::CONVERSATION_STATUS, $conversationStatus));
        $this->addAttribute(AttributeResolver::getAttributeFor(Model::CONVERSATION_VERSION, $conversationVersion));
    }

    /**
     * @param Scene $scene
     * @return $this
     */
    public function addOpeningScene(Scene $scene)
    {
        $this->createOutgoingEdge(Model::HAS_OPENING_SCENE, $scene);
        $this->refresh();
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOpeningScene()
    {
        if ($this->hasOutgoingEdgeWithRelationship(Model::HAS_OPENING_SCENE)) {
            return true;
        }

        return false;
    }

    /**
     * @param Scene $scene
     * @return $this
     */
    public function addScene(Scene $scene)
    {
        $this->createOutgoingEdge(Model::HAS_SCENE, $scene);
        $this->refresh();
        return $this;
    }

    /**
     * @return Map
     */
    public function getOpeningScenes()
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_OPENING_SCENE);
    }

    /**
     * @return Map
     */
    public function getNonOpeningScenes()
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::HAS_SCENE);
    }

    /**
     * @return Map
     */
    public function getAllScenes()
    {
        if (!isset($this->allScenes)) {
            $openingScenes = $this->getOpeningScenes();
            $nonOpeningScenes = $this->getNonOpeningScenes();

            /* @var Map $allScenes */
            $this->allScenes = $openingScenes->merge($nonOpeningScenes);
        }

        return $this->allScenes;
    }

    /**
     * @param $sceneId
     * @return Scene | bool
     */
    public function getScene($sceneId)
    {
        /* @var Map $allScenes */
        $allScenes = $this->getAllScenes();

        if ($allScenes->hasKey($sceneId)) {
            return $allScenes->get($sceneId);
        }

        return false;
    }

    /**
     * @param string $type
     */
    public function setConversationType(string $type)
    {
        /* @var \OpenDialogAi\AttributeEngine\Attributes\StringAttribute $eiType */
        $eiType = $this->getAttribute(Model::EI_TYPE);
        $eiType->setValue($type);
    }

    /**
     * @return string
     */
    public function getConversationStatus(): string
    {
        return $this->getAttributeValue(Model::CONVERSATION_STATUS);
    }

    /**
     * @param string $status
     */
    public function setConversationStatus(string $status): void
    {
        $this->setAttribute(Model::CONVERSATION_STATUS, $status);
    }

    /**
     * @return int
     */
    public function getConversationVersion(): int
    {
        return $this->getAttributeValue(Model::CONVERSATION_VERSION);
    }

    /**
     * @param int $version
     */
    public function setConversationVersion(int $version): void
    {
        $this->setAttribute(Model::CONVERSATION_VERSION, $version);
    }

    /**
     * @return bool
     */
    public function hasUpdateOf(): bool
    {
        return !$this->getNodesConnectedByOutgoingRelationship(Model::UPDATE_OF)->isEmpty();
    }

    /**
     * @return Conversation
     */
    public function getUpdateOf(): Conversation
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::UPDATE_OF)->first()->value;
    }

    /**
     * @param Conversation $previousConversation
     */
    public function setUpdateOf(Conversation $previousConversation): void
    {
        $this->createOutgoingEdge(Model::UPDATE_OF, $previousConversation);
    }

    /**
     * @return bool
     */
    public function hasInstanceOf(): bool
    {
        return !$this->getNodesConnectedByOutgoingRelationship(Model::INSTANCE_OF)->isEmpty();
    }

    /**
     * @return Conversation
     */
    public function getInstanceOf(): Conversation
    {
        return $this->getNodesConnectedByOutgoingRelationship(Model::INSTANCE_OF)->first()->value;
    }

    /**
     * @param Conversation $previousConversation
     */
    public function setInstanceOf(Conversation $previousConversation): void
    {
        $this->createOutgoingEdge(Model::INSTANCE_OF, $previousConversation);
    }

    /**
     * @return Map
     */
    public function getAllIntents(): Map
    {
        if (!isset($this->allIntents)) {
            $intents = new Map();
            $scenes = $this->getAllScenes();
            /* @var Scene $scene */
            foreach ($scenes as $scene) {
                $sceneIntents = $scene->getAllIntents();
                $intents = $intents->merge($sceneIntents);
            }
            $this->allIntents = $intents;
        }

        return $this->allIntents;
    }

    /**
     * @param string $uid
     * @return Intent
     */
    public function getIntentByUid(string $uid): Intent
    {
        $intents = $this->getAllIntents();

        $intents = $intents->filter(function ($key, $value) use ($uid) {
            /* @var Intent $value */
            if ($value->getUid() === $uid) {
                return true;
            }
        });

        if (count($intents) == 1) {
            return $intents->first()->value;
        }

        return null;
    }

    public function getIntentByOrder(int $order): Intent
    {
        $intents = $this->getAllIntents();

        $intents = $intents->filter(function ($key, $value) use ($order) {
            /* @var Intent $value */
            if ($value->getOrder() == $order) {
                return true;
            }
        });

        if (count($intents) == 1) {
            return $intents->first()->value;
        }

        return null;
    }

    /**
     * Clears out the local cache of scenes and intents
     */
    public function refresh()
    {
        unset($this->allScenes, $this->allIntents);
    }
}
