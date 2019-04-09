<?php

namespace OpenDialogAi\Core\Conversation;

use Ds\Map;
use OpenDialogAi\Core\Attribute\StringAttribute;

/**
 * A Conversation is a collection of Scenes.
 */
class Conversation extends NodeWithConditions
{
    public function __construct($id)
    {
        parent::__construct($id);
        $this->addAttribute(new StringAttribute(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE));
    }

    /**
     * @param Scene $scene
     * @return $this
     */
    public function addOpeningScene(Scene $scene)
    {
        $this->createOutgoingEdge(Model::HAS_OPENING_SCENE, $scene);
        return $this;
    }

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
        $openingScenes = $this->getOpeningScenes();
        $nonOpeningScenes = $this->getNonOpeningScenes();

        /* @var Map $allScenes */
        $allScenes = $openingScenes->merge($nonOpeningScenes);

        return $allScenes;
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
}
