<?php

namespace OpenDialogAi\ConversationEngine\ConversationStore\EIModels;

use Ds\Set;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\Core\Conversation\Model;

class EIModelConversation extends EIModelWithConditions
{
    private $id;
    private $uid;
    private $eiType;
    private $conversationStatus;
    private $conversationVersion;

    /* @var EIModelConversation $updateOf */
    private $updateOf;

    /* @var EIModelConversation $instanceOf */
    private $instanceOf;

    /* @var Set $openingScenes */
    private $openingScenes;

    /* @var Set $scenes */
    private $scenes;

    /**
     * This method should indicate whether the given response is valid for this EI Model. If it isn't then the `handle`
     * method will not be run.
     * @param array $response
     * @param null $additionalParameter
     * @return bool
     */
    public static function validate(array $response, $additionalParameter = null): bool
    {
        return parent::validate($response, $additionalParameter)
            && EIModelBase::hasEIType($response, Model::CONVERSATION_USER, Model::CONVERSATION_TEMPLATE)
            && key_exists(Model::ID, $response)
            && key_exists(Model::UID, $response);
    }

    /**
     * This method takes the response and uses it to set up the EI model's attributes.
     * @param array $response
     * @param null $additionalParameter
     * @return EIModel
     * @throws \Exception
     */
    public static function handle(array $response, $additionalParameter = null): EIModel
    {
        $eiModelCreator = app()->make(EIModelCreator::class);

        $conversation = parent::handle($response, $additionalParameter);

        $conversation->setId($response[Model::ID]);
        $conversation->setUid($response[Model::UID]);
        $conversation->setEiType($response[Model::EI_TYPE]);
        $conversation->setConversationStatus($response[Model::CONVERSATION_STATUS]);
        $conversation->setConversationVersion($response[Model::CONVERSATION_VERSION]);

        if (isset($response[Model::UPDATE_OF])) {
            /** @var EIModelConversation $conversation */
            $conversation = $eiModelCreator->createEIModel(EIModelConversation::class, $response[Model::UPDATE_OF]);
            $conversation->setUpdateOf($conversation);
        }

        if (isset($response[Model::INSTANCE_OF])) {
            /** @var EIModelConversation $template */
            $template = $eiModelCreator->createEIModel(EIModelConversation::class, $response[Model::INSTANCE_OF]);
            $conversation->setInstanceOf($template);
        }

        $openingScenes = new Set();
        if (isset($response[Model::HAS_OPENING_SCENE])) {
            foreach ($response[Model::HAS_OPENING_SCENE] as $s) {
                $openingScene = $eiModelCreator->createEIModel(EIModelScene::class, $s, $response);
                $openingScenes->add($openingScene);
            }
        }
        $conversation->setOpeningScenes($openingScenes);

        $scenes = new Set();
        if (isset($response[Model::HAS_SCENE])) {
            foreach ($response[Model::HAS_SCENE] as $s) {
                $scene = $eiModelCreator->createEIModel(EIModelScene::class, $s, $response);
                $scenes->add($scene);
            }
        }
        $conversation->setScenes($scenes);

        return $conversation;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getEiType()
    {
        return $this->eiType;
    }

    /**
     * @param mixed $eiType
     */
    public function setEiType($eiType): void
    {
        $this->eiType = $eiType;
    }

    /**
     * @return mixed
     */
    public function getConversationStatus()
    {
        return $this->conversationStatus;
    }

    /**
     * @param mixed $conversationStatus
     */
    public function setConversationStatus($conversationStatus): void
    {
        $this->conversationStatus = $conversationStatus;
    }

    /**
     * @return mixed
     */
    public function getConversationVersion()
    {
        return $this->conversationVersion;
    }

    /**
     * @param mixed $conversationVersion
     */
    public function setConversationVersion($conversationVersion): void
    {
        $this->conversationVersion = $conversationVersion;
    }

    /**
     * @return Set|null
     */
    public function getOpeningScenes(): ?Set
    {
        return $this->openingScenes;
    }

    /**
     * @param Set $openingScenes
     */
    public function setOpeningScenes(Set $openingScenes): void
    {
        $this->openingScenes = $openingScenes;
    }

    /**
     * @return Set|null
     */
    public function getScenes(): ?Set
    {
        return $this->scenes;
    }

    /**
     * @param Set $scenes
     */
    public function setScenes(Set $scenes): void
    {
        $this->scenes = $scenes;
    }

    /**
     * @param $order
     * @return EIModelIntent
     */
    public function getIntentIdByOrder($order): ?EIModelIntent
    {
        /* @var EIModelIntent $intent */
        foreach ($this->getAllUserSaysIntents() as $intent) {
            if ($intent->getOrder() == $order) {
                return $intent;
            }
        }

        return null;
    }

    /**
     * Gets all the intents said by user across the opening scene and all other scenes
     *
     * @return Set
     */
    private function getAllUserSaysIntents(): Set
    {
        $intents = new Set();

        /* @var EIModelScene $scene */
        foreach ($this->getOpeningScenes() as $scene) {
            $intents = $intents->merge($scene->getAllUserIntents());
        }

        /* @var EIModelScene $scene */
        foreach ($this->getScenes() as $scene) {
            $intents = $intents->merge($scene->getAllUserIntents());
        }

        return $intents;
    }

    /**
     * @return EIModelConversation|null
     */
    public function getUpdateOf(): ?EIModelConversation
    {
        return $this->updateOf;
    }

    /**
     * @param EIModelConversation $updateOf
     */
    private function setUpdateOf(EIModelConversation $updateOf): void
    {
        $this->updateOf = $updateOf;
    }

    /**
     * @return EIModelConversation|null
     */
    public function getInstanceOf(): ?EIModelConversation
    {
        return $this->instanceOf;
    }

    /**
     * @param EIModelConversation $instanceOf
     */
    private function setInstanceOf(EIModelConversation $instanceOf): void
    {
        $this->instanceOf = $instanceOf;
    }
}
